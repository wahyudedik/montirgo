<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {}

    /**
     * Daftar semua chat rooms.
     */
    public function index(Request $request): JsonResponse
    {
        $chats = $this->chatService->getUserChats($request->user());

        return response()->json([
            'data' => $chats->map(fn ($chat) => [
                'id' => $chat->id,
                'order_id' => $chat->order_id,
                'order_code' => $chat->order->code ?? null,
                'partner_name' => $chat->partner->workshop_name ?? null,
                'customer_name' => $chat->user->name ?? null,
                'last_message' => $chat->last_message_at?->diffForHumans(),
                'is_active' => $chat->is_active,
            ]),
        ]);
    }

    /**
     * Buka atau dapatkan chat room untuk order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $chat = $this->chatService->getOrCreateChat($order, $request->user());

        return response()->json([
            'data' => [
                'id' => $chat->id,
                'order_id' => $chat->order_id,
                'is_active' => $chat->is_active,
                'messages' => $this->chatService->getMessages($chat)->map(fn ($msg) => [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $msg->sender->name,
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_url,
                    'attachment_type' => $msg->attachment_type,
                    'is_read' => $msg->is_read,
                    'created_at' => $msg->created_at?->toISOString(),
                ])->values(),
            ],
        ]);
    }

    /**
     * Kirim pesan via API.
     */
    public function send(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required_without:attachment_url|string|max:1000',
            'attachment_url' => 'nullable|string',
            'attachment_type' => 'nullable|in:image,file,location,none',
        ]);

        $chat = $this->chatService->getOrCreateChat($order, $request->user());

        $message = $this->chatService->sendMessage(
            $chat,
            $request->user(),
            $validated['message'] ?? null,
            $validated['attachment_url'] ?? null,
            $validated['attachment_type'] ?? 'none',
        );

        return response()->json([
            'data' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'created_at' => $message->created_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Polling untuk pesan baru.
     */
    public function poll(Request $request, Order $order): JsonResponse
    {
        $chat = $this->chatService->getOrCreateChat($order, $request->user());
        $lastMessageId = $request->integer('last_id', 0);

        $messages = $this->chatService->getMessages($chat, 50, null)
            ->filter(fn ($msg) => $msg->id > $lastMessageId)
            ->values();

        $this->chatService->markAsRead($chat, $request->user());

        return response()->json([
            'data' => $messages->map(fn ($msg) => [
                'id' => $msg->id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'message' => $msg->message,
                'attachment_url' => $msg->attachment_url,
                'created_at' => $msg->created_at?->toISOString(),
            ])->values(),
            'unread_count' => $this->chatService->getUnreadCount($chat, $request->user()),
        ]);
    }
}
