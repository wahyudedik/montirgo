<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Partner;
use App\Models\Review;
use App\Services\NotificationService;

class ReviewObserver
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    /**
     * Handle review created — update rating partner & kirim notifikasi.
     */
    public function created(Review $review): void
    {
        // Update partner rating
        $this->updatePartnerRating($review->partner);

        // Notify partner
        if ($review->partner->user) {
            $this->notificationService->sendInApp(
                $review->partner->user,
                'Review Baru',
                "Kamu mendapat review {$review->rating} bintang dari pelanggan.",
                [
                    'review_id' => $review->id,
                    'rating' => $review->rating,
                ],
                'review',
            );
        }
    }

    /**
     * Handle review updated — update rating partner jika reply berubah.
     */
    public function updated(Review $review): void
    {
        if ($review->wasChanged('partner_reply')) {
            $this->updatePartnerRating($review->partner);
        }
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
}
