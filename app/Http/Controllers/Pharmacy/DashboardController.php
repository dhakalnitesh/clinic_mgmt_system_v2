<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\SalesItem;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today     = today();
        $thisMonth = now()->startOfMonth();

        // ── Today's Sales ──────────────────────────────────────────
        $todaySales = Sales::completed()
            ->whereDate('sale_date', $today)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue')
            ->first();

        // ── This Month ─────────────────────────────────────────────
        $monthSales = Sales::completed()
            ->where('sale_date', '>=', $thisMonth)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_amount),0) as revenue')
            ->first();

        // ── Stock Alerts ───────────────────────────────────────────
        $lowStockCount    = Medicine::active()->lowStock()->count();
        $outOfStockCount  = Medicine::active()
            ->whereDoesntHave('activeBatches')
            ->count();
        $nearExpiryCount  = StockBatch::nearExpiry(90)->count();
        $expiredInStock   = StockBatch::expired()
            ->where('quantity_available', '>', 0)
            ->count();

        // ── Pending Work ───────────────────────────────────────────
        $pendingPrescriptions = Prescription::where('status', 'pending')->count();
        $pendingGrns          = GoodsReceivedNote::where('status', 'pending')->count();
        $pendingPos           = PurchaseOrder::whereIn('status', ['sent','partial'])->count();

        // ── Stock Value ────────────────────────────────────────────
        $stockValue = StockBatch::available()
            ->selectRaw('COALESCE(SUM(quantity_available * purchase_price), 0) as value')
            ->value('value');

        // ── Recent Sales (last 7 days chart data) ──────────────────
        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = today()->subDays($daysAgo);
            $sales = Sales::completed()
                ->whereDate('sale_date', $date)
                ->selectRaw('COALESCE(SUM(total_amount),0) as revenue, COUNT(*) as count')
                ->first();
            return [
                'date'    => $date->format('D'),
                'revenue' => (float) ($sales->revenue ?? 0),
                'count'   => (int)   ($sales->count   ?? 0),
            ];
        })->all();

        // ── Top Selling Medicines (this month) ─────────────────────
        $topMedicines = SalesItem::query()
            ->with('medicine:id,name')
            ->whereHas('sale', fn ($q) =>
                $q->completed()->where('sale_date', '>=', $thisMonth)
            )
            ->selectRaw('medicine_id, SUM(quantity) as qty_sold, SUM(subtotal) as revenue')
            ->groupBy('medicine_id')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'medicine' => $item->medicine?->name,
                'qty_sold' => $item->qty_sold,
                'revenue'  => round($item->revenue, 2),
            ]);

        // ── Near-Expiry Items ──────────────────────────────────────
        $nearExpiryItems = StockBatch::nearExpiry(30)
            ->with('medicine:id,name')
            ->orderBy('expiry_date')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'medicine'           => $b->medicine?->name,
                'batch_number'       => $b->batch_number,
                'expiry_date'        => $b->expiry_date->toDateString(),
                'days_to_expiry'     => $b->days_to_expiry,
                'quantity_available' => $b->quantity_available,
            ]);

        return Inertia::render('Pharmacy/Dashboard', [
            'kpis' => [
                'today_sales_count'    => (int)   $todaySales->count,
                'today_revenue'        => (float) $todaySales->revenue,
                'month_sales_count'    => (int)   $monthSales->count,
                'month_revenue'        => (float) $monthSales->revenue,
                'stock_value'          => (float) $stockValue,
                'low_stock_count'      => $lowStockCount,
                'out_of_stock_count'   => $outOfStockCount,
                'near_expiry_count'    => $nearExpiryCount,
                'expired_in_stock'     => $expiredInStock,
                'pending_prescriptions'=> $pendingPrescriptions,
                'pending_grns'         => $pendingGrns,
                'pending_pos'          => $pendingPos,
            ],
            'last7_days'        => $last7Days,
            'top_medicines'     => $topMedicines,
            'near_expiry_items' => $nearExpiryItems,
        ]);
    }
}