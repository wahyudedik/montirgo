<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Customer atau partner yang mengerjakan order boleh melihat.
     */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            || $order->partner_id === $user->partner?->id;
    }

    /**
     * Customer boleh membuat order.
     */
    public function create(User $user): bool
    {
        return $user->isCustomer();
    }

    /**
     * Hanya customer pemilik order yang boleh membatalkan.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    /**
     * Hanya partner yang ditugaskan boleh menerima/menolak/mengupdate status.
     */
    public function accept(User $user, Order $order): bool
    {
        return $order->partner_id === $user->partner?->id;
    }

    public function reject(User $user, Order $order): bool
    {
        return $order->partner_id === $user->partner?->id;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $order->partner_id === $user->partner?->id;
    }

    /**
     * Partner yang ditugaskan boleh melacak order.
     */
    public function track(User $user, Order $order): bool
    {
        return $order->partner_id === $user->partner?->id;
    }

    /**
     * Customer pemilik, partner yang ditugaskan, atau admin boleh membuat klaim asuransi.
     */
    public function createClaim(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            || $order->partner_id === $user->partner?->id
            || $user->isAdmin();
    }

    /**
     * Customer pemilik, partner yang ditugaskan, atau admin boleh melihat klaim.
     */
    public function viewClaim(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            || $order->partner_id === $user->partner?->id
            || $user->isAdmin();
    }
}
