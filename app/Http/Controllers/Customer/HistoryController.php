<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * Riwayat layanan customer.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with(['partner', 'vehicle', 'payment', 'review'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total_orders' => $user->orders()->count(),
            'completed_orders' => $user->orders()->where('status', 'completed')->count(),
            'total_spent' => (float) $user->orders()
                ->where('status', 'completed')
                ->sum('total_amount'),
            'favorite_service' => $user->orders()
                ->select('service_type', DB::raw('COUNT(*) as count'))
                ->groupBy('service_type')
                ->orderByDesc('count')
                ->value('service_type') ?? '-',
        ];

        return view('customer.history.index', compact('orders', 'stats'));
    }
}
