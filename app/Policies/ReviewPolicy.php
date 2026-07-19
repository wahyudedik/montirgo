<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Hanya partner yang menerima review boleh membalas.
     */
    public function reply(User $user, Review $review): bool
    {
        return $review->partner_id === $user->partner?->id;
    }
}
