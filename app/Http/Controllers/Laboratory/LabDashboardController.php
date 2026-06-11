<?php

namespace App\Http\Controllers\Laboratory;

use App\Http\Controllers\Controller;
use App\Models\Laboratory\LabOrder;
use Inertia\Inertia;

class LabDashboardController extends Controller
{
    public function index()
{
    $relations = [
        'patient',
        'doctor',
        'items.labTest',
    ];

    $stats = [
        'ordered' => LabOrder::where('status', 'ordered')->count(),
        'sample_collected' => LabOrder::where('status', 'sample_collected')->count(),
        'processing' => LabOrder::where('status', 'processing')->count(),
        'completed' => LabOrder::where('status', 'completed')->count(),
        'completedToday' => LabOrder::where('status', 'completed')
    ->whereDate('updated_at', today())
    ->count(),
    ];

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
        'stats' => $stats,
        'pendingOrders' => $pendingOrders,
        'processingOrders' => $processingOrders,
        'recentOrders' => $recentOrders,
    ]);
}
}