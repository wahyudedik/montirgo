<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Review;
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
     * Daftar review untuk partner.
     */
    public function index(Request $request): View
    {
        $partner = $request->user()->partner;

        $reviews = $this->reviewService->getPartnerReviews($partner, 20);

        $stats = $reviews->mapToGroups(function ($review) {
            return [$review->rating => $review];
        });

        $ratingDistribution = collect([5, 4, 3, 2, 1])->mapWithKeys(function ($star) use ($stats) {
            return [$star => $stats->get($star, collect())->count()];
        });

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 0;

        return view('partner.reviews.index', compact(
            'reviews',
            'ratingDistribution',
            'totalReviews',
            'avgRating',
        ));
    }

    /**
     * Partner membalas review.
     */
    public function reply(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'partner_reply' => 'required|string|max:500',
        ]);

        $partner = $request->user()->partner;

        $this->reviewService->replyReview($partner, $review, $validated['partner_reply']);

        return redirect()
            ->route('partner.reviews.index')
            ->with('success', 'Balasan berhasil dikirim.');
    }
}
