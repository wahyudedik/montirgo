<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Dashboard stats untuk admin.
     */
    public function getAdminStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfWeek = $now->copy()->startOfWeek();

        return [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_partners' => Partner::count(),
            'active_partners' => Partner::where('is_online', true)->count(),
            'pending_partners' => Partner::where('status', 'pending')->count(),

            // Orders
            'total_orders' => Order::count(),
            'orders_today' => Order::whereDate('created_at', $now->toDateString())->count(),
            'orders_this_week' => Order::where('created_at', '>=', $startOfWeek)->count(),
            'orders_this_month' => Order::where('created_at', '>=', $startOfMonth)->count(),
            'active_orders' => Order::whereIn('status', ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress'])->count(),

            // Revenue
            'revenue_this_month' => (float) Payment::where('status', 'paid')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('platform_commission'),
            'revenue_total' => (float) Payment::where('status', 'paid')
                ->sum('platform_commission'),

            // Completion rate
            'completion_rate' => $this->getCompletionRate(),
            'avg_rating' => $this->getAvgRating(),
        ];
    }

    /**
     * Revenue chart data (12 bulan terakhir).
     */
    public function getRevenueChart(): array
    {
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'revenue' => (float) Payment::where('status', 'paid')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('platform_commission'),
                'orders' => Order::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ]);
        }

        return $months->toArray();
    }

    /**
     * Order status distribution.
     */
    public function getOrderStatusDistribution(): array
    {
        return Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Top partners by orders.
     */
    public function getTopPartners(int $limit = 5): array
    {
        return Partner::query()
            ->with('user')
            ->select('partners.*', DB::raw('COUNT(orders.id) as order_count'))
            ->leftJoin('orders', 'partners.id', '=', 'orders.partner_id')
            ->where('orders.status', 'completed')
            ->groupBy('partners.id')
            ->orderByDesc('order_count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Peak hours analysis.
     */
    public function getPeakHours(): array
    {
        return Order::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();
    }

    /**
     * Dashboard stats untuk partner.
     */
    public function getPartnerStats(Partner $partner): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        $completedOrders = Order::where('partner_id', $partner->id)
            ->where('status', 'completed');

        return [
            'total_orders' => Order::where('partner_id', $partner->id)->count(),
            'completed_orders' => $completedOrders->count(),
            'orders_this_month' => Order::where('partner_id', $partner->id)
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
            'earnings_this_month' => (float) WalletTransaction::whereHas('order', function ($q) use ($partner) {
                $q->where('partner_id', $partner->id);
            })->where('type', 'credit')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('amount'),
            'avg_rating' => $partner->rating_avg,
            'total_reviews' => $partner->total_reviews,
            'rating_avg' => $partner->rating_avg,
        ];
    }

    private function getCompletionRate(): float
    {
        $total = Order::count();

        if ($total === 0) {
            return 0;
        }

        $completed = Order::where('status', 'completed')->count();

        return round(($completed / $total) * 100, 1);
    }

    private function getAvgRating(): float
    {
        return (float) DB::table('reviews')->avg('rating');
    }
}
