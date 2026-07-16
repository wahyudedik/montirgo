<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vehicle;
use App\Services\EmergencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SosController extends Controller
{
    /**
     * Halaman SOS — pilih kategori darurat.
     */
    public function index(): View
    {
        $user = Auth::user();
        $vehicles = Vehicle::where('user_id', $user->id)->get();
        $categories = EmergencyService::SOS_CATEGORIES;

        // Cek apakah user sedang punya order SOS aktif
        $activeSos = Order::where('user_id', $user->id)
            ->where('is_sos', true)
            ->whereIn('status', ['pending', 'dispatching', 'accepted', 'on_the_way', 'arrived', 'in_progress'])
            ->first();

        return view('customer.sos.index', compact('categories', 'vehicles', 'activeSos'));
    }

    /**
     * Kirim order SOS — flow disederhanakan (satu klik).
     */
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sos_type' => 'required|string|in:flat_tire,dead_battery,out_of_fuel,locked_keys,overheat',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
            'location_address' => 'nullable|string|max:255',
        ], [
            'sos_type.required' => 'Jenis darurat wajib dipilih.',
            'sos_type.in' => 'Jenis darurat tidak valid.',
            'location_lat.required' => 'Lokasi wajib diaktifkan.',
            'location_lng.required' => 'Lokasi wajib diaktifkan.',
        ]);

        $user = Auth::user();
        $sosLabel = EmergencyService::getSosLabel($validated['sos_type']);
        $sosIcon = EmergencyService::getSosIcon($validated['sos_type']);

        // Buat order SOS — callout fee gratis untuk SOS
        $order = Order::create([
            'user_id' => $user->id,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'service_type' => "SOS Darurat - {$sosLabel}",
            'problem_description' => "{$sosIcon} SOS {$sosLabel}: Darurat, butuh penanganan segera!",
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'location_address' => $validated['location_address'] ?? null,
            'callout_fee' => 0,
            'total_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'pending',
            'is_sos' => true,
            'sos_type' => $validated['sos_type'],
        ]);

        // Mulai proses dispatch SOS (priority)
        app(EmergencyService::class)->startDispatch($order);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', "🚨 SOS {$sosLabel} berhasil dikirim! Sedang mencari mekanik terdekat...");
    }

    /**
     * Batalkan order SOS.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_if(
            $order->user_id !== Auth::id(),
            403,
            'Anda tidak memiliki akses ke order ini.'
        );

        if (! in_array($order->status, ['pending', 'dispatching'])) {
            return back()->with('error', 'Order SOS tidak dapat dibatalkan pada status ini.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        $reason = $validated['cancel_reason'] ?? 'Dibatalkan oleh pengguna';
        app(EmergencyService::class)->cancelOrder($order, $reason);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order SOS berhasil dibatalkan.');
    }
}
