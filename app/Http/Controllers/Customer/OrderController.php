<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\Vehicle;
use App\Services\DispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Daftar semua order customer.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Order::forUser($user->id)->with(['partner', 'vehicle']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(10);
        $activeCount = Order::forUser($user->id)->active()->count();

        return view('customer.orders.index', compact('orders', 'activeCount'));
    }

    /**
     * Form buat order baru.
     */
    public function create(): View
    {
        $user = Auth::user();
        $vehicles = Vehicle::where('user_id', $user->id)->get();

        $serviceTypes = [
            'Servis Berkala' => [
                'Servis Berkala Motor',
                'Servis Berkala Mobil',
            ],
            'Ban & Oli' => [
                'Tambal Ban',
                'Ganti Ban',
                'Ganti Oli Mesin',
                'Ganti Oli Gardan',
            ],
            'Mesin & Kelistrikan' => [
                'Tune-up Mesin',
                'Servis Kelistrikan',
                'Ganti Aki/Jumper',
                'Overhaul Mesin',
            ],
            'Derek & Towing' => [
                'Derek Motor',
                'Derek/Towing Mobil',
            ],
            'AC Mobil' => [
                'Servis AC Mobil',
                'Isi Freon AC',
                'Perbaikan Kompresor AC',
            ],
            'SOS Darurat' => [
                'Ban Pecah/Bocor',
                'Aki Soak',
                'Kehabisan Bensin',
                'Kunci Tertinggal',
                'Mesin Overheat/Mogok',
            ],
        ];

        $calloutFee = 25000;

        return view('customer.orders.create', compact('vehicles', 'serviceTypes', 'calloutFee'));
    }

    /**
     * Simpan order baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'service_category' => 'required|string',
            'service_type' => 'required|string',
            'problem_description' => 'nullable|string|max:1000',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
            'location_address' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,wallet,qris,card',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'file|mimes:jpg,jpeg,png,webp,mp4|max:5120',
        ], [
            'vehicle_id.exists' => 'Kendaraan yang dipilih tidak valid.',
            'service_category.required' => 'Kategori layanan wajib dipilih.',
            'service_type.required' => 'Jenis layanan wajib dipilih.',
            'location_lat.required' => 'Lokasi wajib diaktifkan.',
            'location_lat.numeric' => 'Format latitude tidak valid.',
            'location_lng.required' => 'Lokasi wajib diaktifkan.',
            'location_lng.numeric' => 'Format longitude tidak valid.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'photos.max' => 'Maksimal 5 foto/video.',
            'photos.*.max' => 'Ukuran file maksimal 5MB.',
            'photos.*.mimes' => 'Format file tidak didukung.',
        ]);

        $user = Auth::user();
        $calloutFee = 25000;

        $order = Order::create([
            'user_id' => $user->id,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'service_type' => $validated['service_category'].' - '.$validated['service_type'],
            'problem_description' => $validated['problem_description'] ?? null,
            'location_lat' => $validated['location_lat'],
            'location_lng' => $validated['location_lng'],
            'location_address' => $validated['location_address'] ?? null,
            'callout_fee' => $calloutFee,
            'total_amount' => $calloutFee,
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        // Handle photo/video uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $path = $file->store('order-photos', 'public');
                $type = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'photo';

                OrderPhoto::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'photo_url' => $path,
                    'type' => $type,
                    'caption' => null,
                ]);
            }
        }

        // Mulai proses dispatch
        app(DispatchService::class)->startDispatch($order);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order #'.$order->code.' berhasil dibuat! Sedang mencari mekanik terdekat...');
    }

    /**
     * Detail order / tracking.
     */
    public function show(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['partner.user', 'vehicle', 'payment', 'review', 'photos']);

        $statusSteps = [
            'pending' => ['label' => 'Order Dibuat', 'icon' => 'document'],
            'dispatching' => ['label' => 'Mencari Mekanik', 'icon' => 'search'],
            'accepted' => ['label' => 'Mekanik Diterima', 'icon' => 'check'],
            'on_the_way' => ['label' => 'Mekanik Dalam Perjalanan', 'icon' => 'navigation'],
            'arrived' => ['label' => 'Mekanik Tiba', 'icon' => 'location'],
            'in_progress' => ['label' => 'Sedang Dikerjakan', 'icon' => 'wrench'],
            'completed' => ['label' => 'Selesai', 'icon' => 'check-circle'],
        ];

        return view('customer.orders.show', compact('order', 'statusSteps'));
    }

    /**
     * Batalkan order.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);

        if (! in_array($order->status, ['pending', 'dispatching'])) {
            return back()->with('error', 'Order tidak dapat dibatalkan pada status ini.');
        }

        $validated = $request->validate([
            'cancel_reason' => 'required|string|max:255',
        ], [
            'cancel_reason.required' => 'Alasan pembatalan wajib diisi.',
        ]);

        app(DispatchService::class)->cancelOrder($order, $validated['cancel_reason']);

        return redirect()->route('customer.orders.show', $order)
            ->with('success', 'Order #'.$order->code.' berhasil dibatalkan.');
    }

    /**
     * Pastikan order milik user yang sedang login.
     */
    private function authorizeOrder(Order $order): void
    {
        abort_if(
            $order->user_id !== Auth::id(),
            403,
            'Anda tidak memiliki akses ke order ini.'
        );
    }
}
