<?php

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can get notifications', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    NotificationLog::create([
        'user_id' => $user->id,
        'type' => 'order_status',
        'title' => 'Order Selesai',
        'body' => 'Order Anda telah selesai',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/notifications');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'unread_count',
            'data',
        ]);
});

test('notifications returns correct unread count', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    NotificationLog::create([
        'user_id' => $user->id,
        'type' => 'order_status',
        'title' => 'Notif 1',
        'body' => 'Body 1',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    NotificationLog::create([
        'user_id' => $user->id,
        'type' => 'chat',
        'title' => 'Notif 2',
        'body' => 'Body 2',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/notifications');

    $response->assertStatus(200)
        ->assertJsonPath('unread_count', 2);
});

test('user only sees their own notifications', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $token1 = $user1->createToken('test')->plainTextToken;

    NotificationLog::create([
        'user_id' => $user1->id,
        'type' => 'order_status',
        'title' => 'User 1 Notif',
        'body' => 'Body',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    NotificationLog::create([
        'user_id' => $user2->id,
        'type' => 'order_status',
        'title' => 'User 2 Notif',
        'body' => 'Body',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson('/api/v1/notifications');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('authenticated user can mark all notifications as read', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    NotificationLog::create([
        'user_id' => $user->id,
        'type' => 'order_status',
        'title' => 'Unread Notif',
        'body' => 'Body',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/notifications/read-all');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('authenticated user can update fcm token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/fcm-token', [
            'fcm_token' => 'test-fcm-token-12345',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'fcm_token' => 'test-fcm-token-12345',
    ]);
});

test('fcm token requires fcm_token field', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/fcm-token', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['fcm_token']);
});

test('unauthenticated user cannot access notifications', function () {
    $this->getJson('/api/v1/notifications')
        ->assertStatus(401);
});

test('unauthenticated user cannot update fcm token', function () {
    $this->postJson('/api/v1/fcm-token', ['fcm_token' => 'test'])
        ->assertStatus(401);
});
