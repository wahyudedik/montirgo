<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ServiceCostItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceCostController extends Controller
{
    public function index(Request $request): View
    {
        $partner = $request->user()->partner;

        $orders = Order::where('partner_id', $partner?->id)
            ->where('status', 'in_progress')
            ->with(['user', 'serviceCostItems'])
            ->latest()
            ->paginate(10);

        return view('partner.service-cost.index', compact('orders'));
    }

    public function create(Order $order): View
    {
        $partner = request()->user()->partner;

        abort_unless(
            $order->partner_id === $partner?->id && $order->status === 'in_progress',
            403
        );

        $order->load('serviceCostItems');

        return view('partner.service-cost.create', compact('order'));
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $partner = $request->user()->partner;

        abort_unless(
            $order->partner_id === $partner?->id && $order->status === 'in_progress',
            403
        );

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.type' => 'required|in:service,sparepart',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Delete existing items
        $order->serviceCostItems()->delete();

        $totalFee = 0;

        foreach ($validated['items'] as $item) {
            $subtotal = $item['unit_price'] * $item['quantity'];
            $totalFee += $subtotal;

            ServiceCostItem::create([
                'order_id' => $order->id,
                'name' => $item['name'],
                'type' => $item['type'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ]);
        }

        // Update order service fee
        $commissionRate = config('services.montirgo.additional_commission_rate', 0.10);
        $commission = round($totalFee * $commissionRate, 2);
        $order->update([
            'service_fee' => $totalFee,
            'total_amount' => $order->callout_fee + $totalFee,
            'platform_commission' => $order->platform_commission + $commission,
            'partner_earning' => $order->partner_earning + ($totalFee - $commission),
        ]);

        return redirect()->route('partner.service-cost.index')
            ->with('success', 'Rincian biaya servis berhasil disimpan. Total: Rp '.number_format($totalFee, 0, ',', '.'));
    }
}
