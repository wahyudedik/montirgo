<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
    ) {}

    /**
     * Daftar semua chat rooms partner.
     */
    public function index(Request $request): View
    {
        $chats = $this->chatService->getUserChats($request->user());

        return view('partner.chat.index', compact('chats'));
    }

    /**
     * Buka chat room untuk order tertentu.
     */
    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $chat = $this->chatService->getOrCreateChat($order, $request->user());
        $messages = $this->chatService->getMessages($chat);

        // Tandai pesan sudah dibaca
        $this->chatService->markAsRead($chat, $request->user());

        return view('partner.chat.show', compact('chat', 'order', 'messages'));
    }

    /**
     * Kirim pesan via AJAX.
     */
    public function send(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

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
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'created_at' => $message->created_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Polling untuk pesan baru (fallback).
     */
    public function poll(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        $chat = $this->chatService->getOrCreateChat($order, $request->user());
        $lastMessageId = $request->integer('last_id', 0);

        $messages = $this->chatService->getMessages($chat, 50, null)
            ->filter(fn ($msg) => $msg->id > $lastMessageId)
            ->values();

        $this->chatService->markAsRead($chat, $request->user());

        return response()->json([
            'messages' => $messages->map(fn ($msg) => [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'attachment_url' => $msg->attachment_url,
                'attachment_type' => $msg->attachment_type,
                'created_at' => $msg->created_at?->toISOString(),
            ])->values(),
        ]);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_if(
            $order->partner_id !== $request->user()->partner?->id,
            403,
        );
    }
}
