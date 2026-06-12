<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Patient\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BillingController extends Controller
{
    public function invoices(Request $request)
    {
        $query = Invoice::with(['patient', 'items', 'payments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate($request->per_page ?? 10)->withQueryString();
        $patients = Patient::orderBy('name')->get(['id', 'name', 'phone']);

        return Inertia::render('Billing/Invoices', [
            'invoices' => $invoices,
            'patients' => $patients,
            'filters' => $request->only(['search', 'start_date', 'end_date', 'per_page', 'status']),
        ]);
    }

    public function printAllInvoices(Request $request)
    {
        $query = Invoice::with(['patient']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->get();

        return Inertia::render('Billing/PrintInvoiceList', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'start_date', 'end_date', 'status']),
        ]);
    }

    public function payments(Request $request)
    {
        $query = Payment::with(['invoice.patient']);

        if ($request->filled('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        $payments = $query->latest()->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Billing/Payments', [
            'payments' => $payments,
            'filters' => $request->only(['start_date', 'end_date', 'per_page']),
        ]);
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $subtotal = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
        $discount = $validated['discount'] ?? 0;
        $total = max(0, $subtotal - $discount);

        $invoice = Invoice::create([
            'patient_id' => $validated['patient_id'],
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad(Invoice::max('id') + 1 ?? 1, 4, '0', STR_PAD_LEFT),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return back()->with('success', 'Invoice created successfully.');
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,card,online,insurance,other',
            'notes' => 'nullable|string',
        ]);

        $patientId = $invoice->patient_id;
        $receiptCount = Payment::whereHas('invoice', fn($q) => $q->where('patient_id', $patientId))->count() + 1;
        $receiptNumber = 'bill-' . str_pad($patientId, 3, '0', STR_PAD_LEFT) . '-' . str_pad($receiptCount, 3, '0', STR_PAD_LEFT);

        $payment = $invoice->payments()->create([
            'receipt_number' => $receiptNumber,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        $totalPaid = $invoice->payments()->sum('amount');

        if ($totalPaid >= $invoice->total) {
            $invoice->update(['status' => 'paid', 'paid_amount' => $totalPaid]);
        } elseif ($totalPaid > 0) {
            $invoice->update(['status' => 'partial', 'paid_amount' => $totalPaid]);
        }

        return redirect()->route('billing.payments.receipt', $payment->id);
    }

    public function paymentReceipt($paymentId)
    {
        $payment = Payment::with(['invoice.patient', 'invoice.items'])->findOrFail($paymentId);

        return Inertia::render('Billing/PrintPaymentReceipt', [
            'payment' => $payment,
            'invoice' => $payment->invoice,
        ]);
    }

    public function patientPaymentHistory(Patient $patient)
    {
        $payments = Payment::with(['invoice'])
            ->whereHas('invoice', fn($q) => $q->where('patient_id', $patient->id))
            ->latest()
            ->get();

        return Inertia::render('Billing/PatientPaymentHistory', [
            'patient' => $patient->load('payments'),
            'payments' => $payments,
        ]);
    }

    public function printInvoice(Invoice $invoice)
    {
        $invoice->load(['patient', 'items', 'payments']);

        return Inertia::render('Billing/PrintInvoice', [
            'invoice' => $invoice,
        ]);
    }
}
