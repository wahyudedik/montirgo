<?php

use App\Models\Chat;
use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application uses. Broadcast channels give you the ability to broadcast
| events to your application's authenticated users.
|
*/

// ─── Private: User-specific channel ──────────────────
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ─── Private: Order tracking (customer & partner) ────
Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);

    if (! $order) {
        return false;
    }

    // Customer pemilik order atau partner yang handle order
    return $user->id === $order->user_id
        || ($user->isPartner() && $order->partner_id === $user->partner?->id)
        || $user->isAdmin();
});

// ─── Private: Chat room per order ────────────────────
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (! $chat) {
        return false;
    }

    return $user->id === $chat->user_id
        || $user->partner?->id === $chat->partner_id
        || $user->isAdmin();
});

// ─── Presence: Partner location channel ──────────────
Broadcast::channel('partner.{partnerId}.location', function ($user, $partnerId) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'role' => $user->role,
    ];
});
