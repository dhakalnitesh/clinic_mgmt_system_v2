<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pharmacy\StoreSalesRequest;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\Sales;
use App\Services\Pharmacy\DispenseService;
use App\Services\Pharmacy\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    public function __construct(private DispenseService $dispenser) {}

    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $sales = Sales::query()
            ->with(['cashier'])
            ->when($request->search, fn ($q) =>
                $q->where('invoice_number', 'like', "%{$request->search}%")
            )
            ->when($request->status,   fn ($q) => $q->where('status', $request->status))
            ->when($request->type,     fn ($q) => $q->where('sale_type', $request->type))
            ->when($request->date_from,fn ($q) => $q->whereDate('sale_date', '>=', $request->date_from))
            ->when($request->date_to,  fn ($q) => $q->whereDate('sale_date', '<=', $request->date_to))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($s) => [
                'id'             => $s->id,
                'invoice_number' => $s->invoice_number,
                'sale_date'      => $s->sale_date->toDateString(),
                'sale_type'      => $s->sale_type,
                'patient_id'     => $s->patient_id,
                'total_amount'   => $s->total_amount,
                'paid_amount'    => $s->paid_amount,
                'payment_mode'   => $s->payment_mode,
                'status'         => $s->status,
                'cashier'        => $s->cashier?->name,
            ]);

        return Inertia::render('Pharmacy/Sales/Index', [
            'sales'   => $sales,
            'filters' => $request->only(['search', 'status', 'type', 'date_from', 'date_to']),
            'summary' => [
                'today_count'  => Sales::completed()->today()->count(),
                'today_amount' => Sales::completed()->today()->sum('total_amount'),
                'total_count'  => Sales::completed()->count(),
                'returns'      => Sales::where('status', 'returned')->count(),
            ],
        ]);
    }

    // ── Create (POS Page) ──────────────────────────────────────────
    public function create(Request $request): Response
    {
        $prescription = null;
        if ($request->prescription_id) {
            $prescription = Prescription::with(['items.medicine.unit', 'items.generic'])
                ->findOrFail($request->prescription_id);
        }

        return Inertia::render('Pharmacy/Sales/Create', [
            'today'        => now()->toDateString(),
            'prescription' => $prescription ? [
                'id'                  => $prescription->id,
                'prescription_number' => $prescription->prescription_number,
                'patient_id'          => $prescription->patient_id,
                'items' => $prescription->items
                    ->filter(fn ($i) => $i->status !== 'dispensed')
                    ->values()
                    ->map(fn ($i) => [
                        'id'                 => $i->id,
                        'medicine_id'        => $i->medicine_id,
                        'medicine_name'      => $i->medicine?->name,
                        'strength'           => $i->medicine?->strength,
                        'form'               => $i->medicine?->form,
                        'unit'               => $i->medicine?->unit?->abbreviation,
                        'generic_name'       => $i->generic?->name,
                        'dosage_instruction' => $i->dosage_instruction,
                        'quantity_prescribed'=> $i->quantity_prescribed,
                        'quantity_dispensed' => $i->quantity_dispensed,
                        'pending_quantity'   => $i->pending_quantity,
                        'is_substitutable'   => $i->is_substitutable,
                    ]),
            ] : null,
        ]);
    }

    // ── Store (Process Sale) ───────────────────────────────────────
    public function store(StoreSalesRequest $request)
    {
        try {
            $data        = $request->validated();
            $data['cashier_id'] = auth()->id();

            $sale = $this->dispenser->processSale($data);

            return redirect()
                ->route('pharmacy.sales.show', $sale)
                ->with('success', "Sale {$sale->invoice_number} completed successfully.");

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(Sales $sale): Response
    {
        $printData = InvoiceService::buildPrintData($sale);

        $sale->load(['items.medicine.unit', 'items.stockBatch', 'cashier', 'pharmacist', 'prescription', 'salesReturns']);

        return Inertia::render('Pharmacy/Sales/Show', [
            'sale' => [
                'id'               => $sale->id,
                'invoice_number'   => $sale->invoice_number,
                'sale_date'        => $sale->sale_date->toDateString(),
                'created_at'       => $sale->created_at->format('d M Y h:i A'),
                'sale_type'        => $sale->sale_type,
                'patient_id'       => $sale->patient_id,
                'status'           => $sale->status,
                'subtotal'         => $sale->subtotal,
                'discount_type'    => $sale->discount_type,
                'discount_value'   => $sale->discount_value,
                'discount_amount'  => $sale->discount_amount,
                'tax_amount'       => $sale->tax_amount,
                'total_amount'     => $sale->total_amount,
                'paid_amount'      => $sale->paid_amount,
                'change_amount'    => $sale->change_amount,
                'payment_mode'     => $sale->payment_mode,
                'payment_reference'=> $sale->payment_reference,
                'notes'            => $sale->notes,
                'cashier'          => $sale->cashier?->name,
                'pharmacist'       => $sale->pharmacist?->name,
                'prescription_number' => $sale->prescription?->prescription_number,
                'items' => $sale->items->map(fn ($item) => [
                    'id'              => $item->id,
                    'medicine_id'     => $item->medicine_id,
                    'medicine_name'   => $item->medicine?->name,
                    'strength'        => $item->medicine?->strength,
                    'form'            => $item->medicine?->form,
                    'unit'            => $item->medicine?->unit?->abbreviation,
                    'batch_number'    => $item->stockBatch?->batch_number,
                    'expiry_date'     => $item->stockBatch?->expiry_date?->format('M Y'),
                    'quantity'        => $item->quantity,
                    'unit_price'      => $item->unit_price,
                    'discount_percent'=> $item->discount_percent,
                    'tax_percent'     => $item->tax_percent,
                    'subtotal'        => $item->subtotal,
                    'returned_quantity'=> $item->returned_quantity,
                    'returnable_quantity' => $item->returnable_quantity,
                ]),
                'returns' => $sale->salesReturns->map(fn ($r) => [
                    'id'                  => $r->id,
                    'return_number'       => $r->return_number,
                    'return_date'         => $r->return_date->toDateString(),
                    'status'              => $r->status,
                    'total_return_amount' => $r->total_return_amount,
                    'refund_mode'         => $r->refund_mode,
                ]),
            ],
            'print_data' => $printData,
        ]);
    }

    // ── Interaction Check (API) ────────────────────────────────────
    public function checkInteractions(Request $request)
    {
        $medicineIds = $request->input('medicine_ids', []);
        $interactions = $this->dispenser->checkInteractions($medicineIds);

        return response()->json($interactions->map(fn ($i) => [
            'drug_a'      => $i->generic1?->name,
            'drug_b'      => $i->generic2?->name,
            'severity'    => $i->severity,
            'label'       => $i->severity_label,
            'color'       => $i->severity_color,
            'description' => $i->description,
            'management'  => $i->management,
        ]));
    }

    // ── Batch Suggestion (API) ─────────────────────────────────────
    public function suggestBatches(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity'    => 'required|integer|min:1',
        ]);

        try {
            $batches = $this->dispenser->selectBatches(
                $request->medicine_id,
                $request->quantity
            );
            return response()->json(['batches' => $batches, 'sufficient' => true]);
        } catch (\RuntimeException $e) {
            return response()->json(['batches' => [], 'sufficient' => false, 'message' => $e->getMessage()]);
        }
    }
}