<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_partners' => Partner::count(),
            'active_partners' => Partner::where('status', 'approved')->where('is_online', true)->count(),
            'pending_partners' => Partner::where('status', 'pending')->count(),
            'total_orders' => Order::count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'active_orders' => Order::whereIn('status', ['pending', 'dispatching', 'accepted', 'on_the_way', 'in_progress'])->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('platform_commission'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::where('status', 'completed')->whereDate('created_at', today())->sum('platform_commission'),
        ];

        $recentOrders = Order::with(['user', 'partner'])
            ->latest()
            ->take(10)
            ->get();

        $recentPartners = Partner::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentPartners'));
    }
}
