<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Laboratory\LabOrder;
use Inertia\Inertia;

class LabDashboardController extends Controller
{
    public function index()
    {
        $relations = ['patient', 'doctor', 'items.labTest'];

        $stats = [
            'total'            => LabOrder::count(),
            'ordered'          => LabOrder::where('status', 'ordered')->count(),
            'sample_collected' => LabOrder::where('status', 'sample_collected')->count(),
            'processing'       => LabOrder::where('status', 'processing')->count(),
            'completed'        => LabOrder::where('status', 'completed')->count(),
            'completedToday'   => LabOrder::where('status', 'completed')
                ->whereDate('updated_at', today())->count(),
            'orderedToday'     => LabOrder::whereDate('created_at', today())->count(),
        ];

        $statusDistribution = collect(['ordered', 'sample_collected', 'processing', 'completed', 'cancelled'])
            ->map(fn ($s) => [
                'status' => $s,
                'count'  => LabOrder::where('status', $s)->count(),
            ]);

        $weeklyData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $nepaliDate = null;
            try {
                $nepaliDate = \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from($date->format('Y-m-d'))->toNepaliDate('d F Y');
            } catch (\Throwable) {
                $nepaliDate = $date->format('M d');
            }
            $weeklyData->push([
                'date'        => $date->format('Y-m-d'),
                'label'       => $date->format('D'),
                'nepali_date' => $nepaliDate,
                'completed'   => LabOrder::where('status', 'completed')
                    ->whereDate('updated_at', $date)->count(),
                'new'         => LabOrder::whereDate('created_at', $date)->count(),
            ]);
        }

        $pendingOrders = LabOrder::with($relations)
            ->where('status', 'ordered')
            ->latest()
            ->take(10)
            ->get();

        $processingOrders = LabOrder::with($relations)
            ->where('status', 'processing')
            ->latest()
            ->take(10)
            ->get();

        $recentOrders = LabOrder::with($relations)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Laboratory/Dashboard', [
            'stats'              => $stats,
            'statusDistribution' => $statusDistribution,
            'weeklyData'         => $weeklyData,
            'pendingOrders'      => $pendingOrders,
            'processingOrders'   => $processingOrders,
            'recentOrders'       => $recentOrders,
        ]);
    }
}
