<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sparepart;
use App\Models\User;

class SparepartPolicy
{
    /**
     * Hanya partner pemilik sparepart yang boleh mengakses.
     */
    public function view(User $user, Sparepart $sparepart): bool
    {
        return $sparepart->partner_id === $user->partner?->id;
    }

    public function update(User $user, Sparepart $sparepart): bool
    {
        return $sparepart->partner_id === $user->partner?->id;
    }

    public function delete(User $user, Sparepart $sparepart): bool
    {
        return $sparepart->partner_id === $user->partner?->id;
    }

    public function toggleActive(User $user, Sparepart $sparepart): bool
    {
        return $sparepart->partner_id === $user->partner?->id;
    }
}
