<?php

use App\Events\NewChatMessage;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake([NewChatMessage::class]);
});

test('customer can open chat room for their order', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order->id}/chat");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'order_id', 'is_active', 'messages'],
        ]);
});

test('customer can send chat message', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/chat/send", [
            'message' => 'Halo, saya di depan Indomaret',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'message', 'sender_id', 'created_at'],
        ])
        ->assertJsonPath('data.message', 'Halo, saya di depan Indomaret');

    $this->assertDatabaseHas('chat_messages', [
        'sender_id' => $user->id,
        'message' => 'Halo, saya di depan Indomaret',
    ]);
});

test('chat message requires either message or attachment', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/chat/send", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['message']);
});

test('customer can poll for new messages', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'accepted',
    ]);

    $chat = Chat::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'is_active' => true,
    ]);

    ChatMessage::create([
        'chat_id' => $chat->id,
        'sender_id' => $partner->user->id,
        'message' => 'Halo, saya sedang dalam perjalanan',
        'is_read' => false,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order->id}/chat/poll");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data',
            'unread_count',
        ]);
});

test('customer can list their chat rooms', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    Chat::create([
        'order_id' => $order->id,
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'is_active' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/orders/'.$order->id.'/chat');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'order_id', 'is_active'],
        ]);
});
