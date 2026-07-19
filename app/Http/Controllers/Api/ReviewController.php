<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateReviewRequest;
use App\Http\Requests\Api\ReplyReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {}

    /**
     * Reviews milik user yang sedang login (customer).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['order', 'partner'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ReviewResource::collection($reviews);
    }

    /**
     * Buat review untuk order yang sudah selesai.
     */
    public function store(CreateReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $review = $this->reviewService->createReview(
            user: $request->user(),
            order: Order::findOrFail($validated['order_id']),
            rating: $validated['rating'],
            comment: $validated['comment'] ?? null,
        );

        return response()->json([
            'message' => 'Review berhasil dikirim',
            'review' => new ReviewResource($review->load(['order', 'partner'])),
        ], 201);
    }

    /**
     * Reviews untuk partner tertentu (public).
     */
    public function partnerReviews(Partner $partner): AnonymousResourceCollection
    {
        $reviews = $this->reviewService->getPartnerReviews($partner);

        return ReviewResource::collection($reviews);
    }

    /**
     * Reviews yang diterima partner (untuk partner yang login).
     */
    public function partnerIndex(Request $request): AnonymousResourceCollection
    {
        $partner = $request->user()->partner;

        if (! $partner) {
            return response()->json(['message' => 'Akun ini bukan partner'], 404);
        }

        $reviews = $this->reviewService->getPartnerReviews($partner);

        return ReviewResource::collection($reviews);
    }

    /**
     * Partner membalas review.
     */
    public function reply(ReplyReviewRequest $request, Review $review): JsonResponse
    {
        $this->authorize('reply', $review);

        $partner = $request->user()->partner;

        $validated = $request->validated();

        $review = $this->reviewService->replyReview($partner, $review, $validated['reply']);

        return response()->json([
            'message' => 'Balasan berhasil dikirim',
            'review' => new ReviewResource($review->load(['user', 'order'])),
        ]);
    }

    /**
     * Stats review (rating distribution).
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'data' => $this->reviewService->getReviewStats(),
        ]);
    }
}
