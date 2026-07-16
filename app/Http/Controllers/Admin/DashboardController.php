<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {}

    public function index(Request $request): View
    {
        $stats = $this->analytics->getAdminStats();
        $revenueChart = $this->analytics->getRevenueChart();
        $statusDistribution = $this->analytics->getOrderStatusDistribution();
        $topPartners = $this->analytics->getTopPartners(5);
        $peakHours = $this->analytics->getPeakHours();

        $recentOrders = Order::with(['user', 'partner'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'revenueChart',
            'statusDistribution',
            'topPartners',
            'peakHours',
            'recentOrders',
        ));
    }
}
