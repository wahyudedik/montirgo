<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PartnerService;
use App\Models\User;

class PartnerServicePolicy
{
    /**
     * Hanya partner pemilik layanan yang boleh mengakses.
     */
    public function update(User $user, PartnerService $service): bool
    {
        return $service->partner_id === $user->partner?->id;
    }

    public function delete(User $user, PartnerService $service): bool
    {
        return $service->partner_id === $user->partner?->id;
    }

    public function toggleActive(User $user, PartnerService $service): bool
    {
        return $service->partner_id === $user->partner?->id;
    }
}
