<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\StockBatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Pharmacy/Reports/Index');
    }

    public function sales(Request $request): Response
    {
        $sales = Sales::query()
            ->with(['cashier', 'items'])
            ->when($request->from, fn ($q) => $q->where('sale_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('sale_date', '<=', $request->to))
            ->orderByDesc('sale_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pharmacy/Reports/Sales', [
            'sales'   => $sales,
            'filters' => $request->only(['from', 'to']),
        ]);
    }

    public function stock(Request $request): Response
    {
        $medicines = Medicine::query()
            ->with(['category', 'generic', 'unit'])
            ->when($request->search, fn ($q) => $q->search($request->search))
            ->when($request->stock === 'low', fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pharmacy/Reports/Stock', [
            'medicines' => $medicines,
            'filters'   => $request->only(['search', 'stock']),
        ]);
    }

    public function expiry(Request $request): Response
    {
        $batches = StockBatch::query()
            ->with(['medicine', 'supplier'])
            ->when($request->days, fn ($q) => $q->nearExpiry($request->days))
            ->when($request->expired === 'yes', fn ($q) => $q->expired())
            ->orderBy('expiry_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pharmacy/Reports/Expiry', [
            'batches' => $batches,
            'filters' => $request->only(['days', 'expired']),
        ]);
    }

    public function purchases(Request $request): Response
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'orderedBy'])
            ->when($request->from, fn ($q) => $q->where('order_date', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->where('order_date', '<=', $request->to))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('order_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pharmacy/Reports/Purchases', [
            'purchase_orders' => $purchaseOrders,
            'filters'         => $request->only(['from', 'to', 'status']),
        ]);
    }

    public function slowMoving(Request $request): Response
    {
        $medicines = Medicine::query()
            ->with(['category', 'generic', 'unit'])
            ->whereDoesntHave('salesItems', fn ($q) =>
                $q->whereHas('sale', fn ($s) => $s->where('sale_date', '>=', now()->subDays($request->days ?? 90)))
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Pharmacy/Reports/SlowMoving', [
            'medicines' => $medicines,
            'filters'   => $request->only(['days']),
        ]);
    }
}
