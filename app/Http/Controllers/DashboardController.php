<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->isAdmin() => $this->adminDashboard($user),
            $user->isPartner() => $this->partnerDashboard($user),
            default => $this->customerDashboard($user),
        };
    }

    private function customerDashboard($user): View
    {
        $vehicles = $user->vehicles()->latest()->get();

        $recentOrders = $user->orders()
            ->with(['partner', 'vehicle'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_orders' => $user->orders()->count(),
            'completed_orders' => $user->orders()->where('status', 'completed')->count(),
            'active_orders' => $user->orders()->whereIn('status', ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress'])->count(),
        ];

        $walletBalance = $user->walletBalance?->balance ?? 0;

        return view('customer.dashboard', compact('user', 'vehicles', 'recentOrders', 'stats', 'walletBalance'));
    }

    private function partnerDashboard($user): View
    {
        $partner = $user->partner;

        $pendingOrders = $partner
            ? $partner->orders()->where('status', 'pending')->count()
            : 0;

        $recentOrders = $partner
            ? $partner->orders()
                ->with(['user', 'vehicle'])
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $stats = [
            'total_orders' => $partner ? $partner->orders()->count() : 0,
            'completed_orders' => $partner ? $partner->orders()->where('status', 'completed')->count() : 0,
            'active_orders' => $partner ? $partner->orders()->whereIn('status', ['accepted', 'on_the_way', 'arrived', 'in_progress'])->count() : 0,
            'rating_avg' => $partner?->rating_avg ?? 0,
            'total_reviews' => $partner?->total_orders ?? 0,
        ];

        $walletBalance = $user->walletBalance?->balance ?? 0;
        $totalIncome = $user->walletBalance?->total_income ?? 0;

        $services = $partner ? $partner->services()->where('is_active', true)->get() : collect();

        return view('partner.dashboard', compact('user', 'partner', 'pendingOrders', 'recentOrders', 'stats', 'walletBalance', 'totalIncome', 'services'));
    }

    private function adminDashboard($user): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }
}
