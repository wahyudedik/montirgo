<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard stats untuk admin.
     *
     * Endpoint API untuk mendapatkan statistik dashboard admin:
     * total users, partners, orders, revenue, completion rate, dll.
     */
    public function stats(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $stats = $analytics->getAdminStats();

        return response()->json([
            'data' => $stats,
        ]);
    }

    /**
     * Revenue chart data (12 bulan terakhir).
     */
    public function revenueChart(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $chart = $analytics->getRevenueChart();

        return response()->json([
            'data' => $chart,
        ]);
    }

    /**
     * Order status distribution.
     */
    public function orderStatusDistribution(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $distribution = $analytics->getOrderStatusDistribution();

        return response()->json([
            'data' => $distribution,
        ]);
    }

    /**
     * Top partners by completed orders.
     */
    public function topPartners(Request $request, AnalyticsService $analytics): JsonResponse
    {
        $limit = (int) $request->query('limit', 5);
        $partners = $analytics->getTopPartners($limit);

        return response()->json([
            'data' => $partners,
        ]);
    }
}
