<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function __construct(
        private ReviewService $reviewService,
    ) {}

    /**
     * Daftar review yang sudah ditulis customer.
     */
    public function index(Request $request): View
    {
        $reviews = $request->user()
            ->reviews()
            ->with(['partner', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.reviews.index', compact('reviews'));
    }

    /**
     * Form untuk menulis review.
     */
    public function create(Order $order): View
    {
        abort_unless($order->status === 'completed', 422, 'Hanya bisa review order yang sudah selesai');
        abort_unless($order->user_id === auth()->id(), 403);
        abort_if($order->review()->exists(), 422, 'Order ini sudah direview');

        $order->load('partner');

        return view('customer.reviews.create', compact('order'));
    }

    /**
     * Simpan review baru.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $this->reviewService->createReview(
            $request->user(),
            $order,
            $validated['rating'],
            $validated['comment'] ?? null,
        );

        return redirect()
            ->route('customer.reviews.index')
            ->with('success', 'Review berhasil dikirim. Terima kasih!');
    }
}
