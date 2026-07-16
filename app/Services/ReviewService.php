<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Partner;
use App\Models\Review;
use App\Models\User;

class ReviewService
{
    /**
     * Buat review untuk order yang sudah selesai.
     */
    public function createReview(User $user, Order $order, int $rating, ?string $comment = null): Review
    {
        abort_if($order->user_id !== $user->id, 403);
        abort_if($order->status !== 'completed', 422, 'Hanya bisa review order yang sudah selesai');
        abort_if($order->review()->exists(), 422, 'Order ini sudah direview');

        $review = Review::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'partner_id' => $order->partner_id,
            'rating' => $rating,
            'comment' => $comment,
        ]);

        // Update partner rating aggregate
        $this->updatePartnerRating($order->partner);

        return $review;
    }

    /**
     * Partner membalas review.
     */
    public function replyReview(Partner $partner, Review $review, string $reply): Review
    {
        abort_if($review->partner_id !== $partner->id, 403);

        $review->update([
            'partner_reply' => $reply,
            'replied_at' => now(),
        ]);

        return $review->fresh();
    }

    /**
     * Update rating rata-rata partner.
     */
    private function updatePartnerRating(Partner $partner): void
    {
        $stats = Review::where('partner_id', $partner->id)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        $partner->update([
            'rating_avg' => round((float) $stats->avg_rating, 1),
            'total_reviews' => (int) $stats->total_reviews,
        ]);
    }

    /**
     * Dapatkan reviews untuk partner.
     */
    public function getPartnerReviews(Partner $partner, int $limit = 20)
    {
        return Review::where('partner_id', $partner->id)
            ->with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Dapatkan stats review untuk admin dashboard.
     */
    public function getReviewStats(): array
    {
        $stats = Review::selectRaw('
            COUNT(*) as total_reviews,
            AVG(rating) as avg_rating,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_stars,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_stars,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_stars,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_stars,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
        ')->first();

        return [
            'total_reviews' => (int) $stats->total_reviews,
            'avg_rating' => round((float) $stats->avg_rating, 1),
            'distribution' => [
                5 => (int) $stats->five_stars,
                4 => (int) $stats->four_stars,
                3 => (int) $stats->three_stars,
                2 => (int) $stats->two_stars,
                1 => (int) $stats->one_star,
            ],
        ];
    }
}
