<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\NewChatMessage;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatService
{
    /**
     * Dapatkan atau buat chat room untuk order.
     */
    public function getOrCreateChat(Order $order, User $user): Chat
    {
        $partnerId = $order->partner_id;

        return Chat::firstOrCreate(
            [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'partner_id' => $partnerId,
            ],
            [
                'is_active' => true,
            ]
        );
    }

    /**
     * Kirim pesan baru.
     */
    public function sendMessage(Chat $chat, User $sender, ?string $text = null, ?string $attachmentUrl = null, string $attachmentType = 'none'): ChatMessage
    {
        $message = DB::transaction(function () use ($chat, $sender, $text, $attachmentUrl, $attachmentType) {
            $msg = ChatMessage::create([
                'chat_id' => $chat->id,
                'sender_id' => $sender->id,
                'message' => $text,
                'attachment_url' => $attachmentUrl,
                'attachment_type' => $attachmentType,
                'is_read' => false,
            ]);

            $chat->update(['last_message_at' => now()]);

            return $msg;
        });

        // Broadcast pesan real-time
        broadcast(new NewChatMessage($message));

        return $message;
    }

    /**
     * Tandai pesan sudah dibaca.
     */
    public function markAsRead(Chat $chat, User $user): int
    {
        return ChatMessage::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Tutup chat room.
     */
    public function closeChat(Chat $chat): void
    {
        $chat->update(['is_active' => false]);
    }

    /**
     * Hitung unread messages untuk user tertentu.
     */
    public function getUnreadCount(Chat $chat, User $user): int
    {
        return ChatMessage::where('chat_id', $chat->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Dapatkan semua chats untuk user dengan info terakhir.
     */
    public function getUserChats(User $user)
    {
        return Chat::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('partner_id', $user->partner?->id);
            })
            ->where('is_active', true)
            ->with(['order', 'user', 'partner.user'])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    /**
     * Dapatkan chat messages dengan pagination.
     */
    public function getMessages(Chat $chat, int $limit = 50, ?int $beforeId = null)
    {
        $query = ChatMessage::where('chat_id', $chat->id)
            ->with('sender')
            ->orderBy('created_at', 'desc');

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        return $query->limit($limit)->get()->reverse()->values();
    }
}
