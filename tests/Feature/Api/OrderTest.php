<?php

use App\Events\NewChatMessage;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use App\Services\DispatchService;
use App\Services\EmergencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake([NewChatMessage::class, OrderStatusChanged::class]);
    Queue::fake();
});

test('customer can create order', function () {
    $this->mock(DispatchService::class, fn ($mock) => $mock->shouldReceive('startDispatch')->once());

    $user = User::factory()->customer()->create([
        'phone' => '081234567890',
        'avatar' => 'https://example.com/avatar.jpg',
    ]);
    // Add vehicle so profile completion >= 80%
    $user->vehicles()->create([
        'type' => 'motorcycle',
        'brand' => 'Honda',
        'model' => 'Vario',
        'license_plate' => 'L 1234 AB',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', [
            'service_type' => 'Tire Change',
            'problem_description' => 'Ban depan kiri bocor',
            'location_lat' => -6.15,
            'location_lng' => 106.80,
            'location_address' => 'Jl. Mojokerto No. 123',
            'payment_method' => 'qris',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'order' => ['id', 'code', 'status', 'payment_method'],
        ]);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'service_type' => 'Tire Change',
        'payment_method' => 'qris',
        'callout_fee' => 30000,
    ]);
});

test('customer cannot create order without required fields', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['service_type', 'location_lat', 'location_lng', 'payment_method']);
});

test('customer cannot create order with invalid payment method', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', [
            'service_type' => 'Tire Change',
            'location_lat' => -6.15,
            'location_lng' => 106.80,
            'payment_method' => 'cash',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['payment_method']);
});

test('unauthenticated user cannot create order', function () {
    $response = $this->postJson('/api/v1/orders', [
        'service_type' => 'Tire Change',
        'location_lat' => -6.15,
        'location_lng' => 106.80,
        'payment_method' => 'qris',
    ]);

    $response->assertStatus(401);
});

test('customer can list their orders', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    Order::factory()->count(3)->create(['user_id' => $user->id]);
    Order::factory()->count(2)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/orders');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('customer can view order detail', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create(['user_id' => $user->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $order->id);
});

test('customer cannot view other user order', function () {
    $user = User::factory()->customer()->create();
    $otherUser = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertStatus(403);
});

test('customer can cancel pending order', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->mock(DispatchService::class, fn ($mock) => $mock->shouldReceive('cancelOrder')->once());

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/orders/{$order->id}/cancel");

    $response->assertStatus(200);
});

test('customer cannot cancel completed order', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->completed()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/orders/{$order->id}/cancel");

    $response->assertStatus(422);
});

test('partner can list their orders', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    Order::factory()->count(2)->create(['partner_id' => $partner->id]);
    Order::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/orders');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('partner can accept dispatching order', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $this->mock(DispatchService::class, fn ($mock) => $mock->shouldReceive('acceptOrder')->once());

    $order = Order::factory()->create([
        'partner_id' => $partner->id,
        'status' => 'dispatching',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/accept");

    $response->assertStatus(200);
});

test('partner cannot accept non-dispatching order', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'partner_id' => $partner->id,
        'status' => 'pending',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/accept");

    $response->assertStatus(422);
});

test('partner can update order status through valid transitions', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'partner_id' => $partner->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/status", [
            'status' => 'on_the_way',
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'on_the_way']);
});

test('partner cannot skip status transitions', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'partner_id' => $partner->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/status", [
            'status' => 'completed',
        ]);

    $response->assertStatus(422);
});

test('partner cannot update status of order not assigned to them', function () {
    $partner = Partner::factory()->create();
    $otherPartner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'partner_id' => $otherPartner->id,
        'status' => 'accepted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/orders/{$order->id}/status", [
            'status' => 'on_the_way',
        ]);

    $response->assertStatus(403);
});

test('customer can create SOS order', function () {
    $this->mock(EmergencyService::class, fn ($mock) => $mock->shouldReceive('startDispatch')->once());

    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/sos', [
            'sos_type' => 'flat_tire',
            'location_lat' => -6.15,
            'location_lng' => 106.80,
            'location_address' => 'Jl. Darurat No. 1',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'order' => ['id', 'is_sos', 'sos_type'],
        ]);

    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'is_sos' => true,
        'sos_type' => 'flat_tire',
        'callout_fee' => 0,
    ]);
});

test('customer cannot create SOS order with invalid type', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/sos', [
            'sos_type' => 'invalid_type',
            'location_lat' => -6.15,
            'location_lng' => 106.80,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sos_type']);
});

test('order has auto-generated code', function () {
    $this->mock(DispatchService::class, fn ($mock) => $mock->shouldReceive('startDispatch')->once());

    $user = User::factory()->customer()->create([
        'phone' => '081234567891',
        'avatar' => 'https://example.com/avatar.jpg',
    ]);
    $user->vehicles()->create([
        'type' => 'motorcycle',
        'brand' => 'Yamaha',
        'model' => 'Mio',
        'license_plate' => 'L 5678 CD',
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/orders', [
            'service_type' => 'Oil Change',
            'location_lat' => -6.15,
            'location_lng' => 106.80,
            'payment_method' => 'ewallet',
        ]);

    $response->assertStatus(201);

    $order = Order::where('user_id', $user->id)->first();
    expect($order->code)->toStartWith('MTG-');
});
