<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DispatchService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Daftar order untuk partner (incoming + history).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $partner = $user->partner;

        $query = Order::where('partner_id', $partner->id)->with(['user', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(10);

        $pendingCount = Order::where('partner_id', $partner->id)
            ->where('status', 'dispatching')
            ->count();

        return view('partner.orders.index', compact('orders', 'pendingCount'));
    }

    /**
     * Detail order untuk partner.
     */
    public function show(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['user', 'vehicle', 'payment', 'review']);

        return view('partner.orders.show', compact('order'));
    }

    /**
     * Partner menerima order.
     */
    public function accept(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        $user = Auth::user();
        $partner = $user->partner;

        if ($order->status !== 'dispatching' || $order->partner_id !== $partner->id) {
            return back()->with('error', 'Order tidak dapat diterima.');
        }

        app(DispatchService::class)->acceptOrder($order, $partner);

        return redirect()->route('partner.orders.show', $order)
            ->with('success', 'Order #'.$order->code.' berhasil diterima! Segera menuju lokasi customer.');
    }

    /**
     * Partner menolak order.
     */
    public function reject(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        $user = Auth::user();
        $partner = $user->partner;

        if ($order->status !== 'dispatching' || $order->partner_id !== $partner->id) {
            return back()->with('error', 'Order tidak dapat ditolak.');
        }

        app(DispatchService::class)->rejectOrder($order, $partner);

        return redirect()->route('partner.orders.index')
            ->with('success', 'Order #'.$order->code.' telah ditolak.');
    }

    /**
     * Partner update status order (on_the_way, arrived, in_progress, completed).
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        $validated = $request->validate([
            'status' => 'required|in:on_the_way,arrived,in_progress,completed',
            'service_fee' => 'required_if:status,completed|nullable|numeric|min:0',
        ]);

        $newStatus = $validated['status'];

        $allowedTransitions = [
            'accepted' => ['on_the_way'],
            'on_the_way' => ['arrived'],
            'arrived' => ['in_progress'],
            'in_progress' => ['completed'],
        ];

        $allowed = $allowedTransitions[$order->status] ?? [];

        if (! in_array($newStatus, $allowed)) {
            return back()->with('error', 'Transisi status tidak valid.');
        }

        if ($newStatus === 'completed' && (empty($validated['service_fee']) || $validated['service_fee'] <= 0)) {
            return back()->with('error', 'Biaya servis harus diisi saat menyelesaikan order.');
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'on_the_way') {
            $updates['started_at'] = now();
        } elseif ($newStatus === 'completed') {
            $updates['completed_at'] = now();

            // Proses pembayaran
            $paymentService = app(PaymentService::class);
            $paymentService->processCompletion($order, (float) $validated['service_fee']);

            // Untuk cash, langsung konfirmasi pembayaran
            if ($order->payment_method === 'cash') {
                $payment = $order->payment;
                if ($payment) {
                    $paymentService->confirmPayment($payment);
                }
            }
        }

        $oldStatus = $order->status;
        $order->update($updates);

        $statusLabel = match ($newStatus) {
            'on_the_way' => 'Dalam Perjalanan',
            'arrived' => 'Tiba di Lokasi',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            default => $newStatus,
        };

        // Broadcast status change real-time
        broadcast(new OrderStatusChanged($order, $oldStatus, $newStatus));

        // Kirim notifikasi ke customer
        if ($order->user) {
            app(NotificationService::class)->notifyOrderStatus(
                $order->user,
                $order->code,
                $newStatus,
                $statusLabel,
            );
        }

        return redirect()->route('partner.orders.show', $order)
            ->with('success', "Status order #{$order->code} diperbarui: {$statusLabel}");
    }

    /**
     * Pastikan order milik partner yang sedang login.
     */
    private function authorizeOrder(Order $order): void
    {
        $partner = Auth::user()->partner;

        abort_if(
            ! $partner || $order->partner_id !== $partner->id,
            403,
            'Anda tidak memiliki akses ke order ini.'
        );
    }
}
