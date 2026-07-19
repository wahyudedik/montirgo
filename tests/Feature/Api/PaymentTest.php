<?php

use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payment status endpoint returns order payment info', function () {
    $order = Order::factory()->create([
        'code' => 'MTG-TEST01',
        'callout_fee' => 30000,
        'service_fee' => 100000,
        'total_amount' => 130000,
        'payment_method' => 'qris',
        'payment_status' => 'pending',
    ]);

    $response = $this->getJson('/api/v1/payment/status/MTG-TEST01');

    $response->assertStatus(200)
        ->assertJson([
            'order_code' => 'MTG-TEST01',
            'payment_status' => 'pending',
            'callout_fee' => 30000,
            'service_fee' => 100000,
            'method' => 'qris',
        ]);
});

test('payment status endpoint returns 404 for unknown order', function () {
    $response = $this->getJson('/api/v1/payment/status/MTG-NONEXIST');

    $response->assertStatus(404);
});

test('customer can create payment for their order', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_method' => 'qris',
    ]);

    $this->mock(MidtransService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        $mock->shouldReceive('createPaymentToken')->once()->andReturn([
            'token' => 'midtrans-snap-token-123',
            'redirect_url' => 'https://app.midtrans.com/snap/v2/vtweb/abc',
        ]);
    });

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/pay");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'payment' => ['id', 'amount', 'status'],
            'snap_token',
            'redirect_url',
        ])
        ->assertJsonPath('payment.status', 'pending')
        ->assertJsonPath('snap_token', 'midtrans-snap-token-123');
});

test('customer cannot create payment for other user order', function () {
    $user = User::factory()->customer()->create();
    $otherUser = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'user_id' => $otherUser->id,
        'status' => 'pending',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/pay");

    $response->assertStatus(403);
});

test('customer cannot pay already completed order', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->completed()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/pay");

    $response->assertStatus(422);
});

test('webhook endpoint accepts valid callback', function () {
    Order::factory()->create([
        'code' => 'MTG-WHOOK',
        'payment_method' => 'qris',
        'payment_status' => 'pending',
    ]);

    $this->mock(MidtransService::class, function ($mock) {
        $mock->shouldReceive('verifyCallbackSignature')->once()->andReturn(true);
        $mock->shouldReceive('mapTransactionStatus')->once()->andReturn('paid');
    });

    $response = $this->postJson('/api/v1/payment/webhook', [
        'order_id' => 'MTG-WHOOK',
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'transaction_id' => 'midtrans-txn-123',
        'gross_amount' => '30000.00',
        'signature_key' => 'valid-signature',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Payment status updated',
            'order_code' => 'MTG-WHOOK',
            'status' => 'paid',
        ]);
});

test('webhook endpoint rejects invalid signature', function () {
    Order::factory()->create([
        'code' => 'MTG-WHOOK2',
        'payment_method' => 'qris',
    ]);

    $this->mock(MidtransService::class, function ($mock) {
        $mock->shouldReceive('verifyCallbackSignature')->once()->andReturn(false);
    });

    $response = $this->postJson('/api/v1/payment/webhook', [
        'order_id' => 'MTG-WHOOK2',
        'transaction_status' => 'settlement',
        'signature_key' => 'invalid-signature',
    ]);

    $response->assertStatus(401);
});

test('webhook endpoint returns 400 without order_id', function () {
    $response = $this->postJson('/api/v1/payment/webhook', [
        'transaction_status' => 'settlement',
    ]);

    $response->assertStatus(400);
});

test('webhook endpoint returns 404 for unknown order', function () {
    $this->mock(MidtransService::class, function ($mock) {
        $mock->shouldReceive('verifyCallbackSignature')->once()->andReturn(true);
    });

    $response = $this->postJson('/api/v1/payment/webhook', [
        'order_id' => 'MTG-NONEXIST',
        'transaction_status' => 'settlement',
    ]);

    $response->assertStatus(404);
});

test('payment service calculate fees correctly', function () {
    $service = new PaymentService;

    // Low fee (< 200k) = 5% commission
    $fees = $service->calculateFees(100000);
    expect($fees['callout_fee'])->toBe(30000.00);
    expect($fees['commission_percent'])->toBe(5);
    expect(round($fees['platform_commission'], 2))->toBe(5000.00);
    expect(round($fees['partner_earning'], 2))->toBe(95000.00);
    expect(round($fees['total_amount'], 2))->toBe(130000.00);

    // Medium fee (200k-500k) = 7% commission
    $fees = $service->calculateFees(300000);
    expect($fees['commission_percent'])->toBe(7);
    expect(round($fees['platform_commission'], 2))->toBe(21000.00);
    expect(round($fees['partner_earning'], 2))->toBe(279000.00);

    // High fee (>= 500k) = 10% commission
    $fees = $service->calculateFees(500000);
    expect($fees['commission_percent'])->toBe(10);
    expect(round($fees['platform_commission'], 2))->toBe(50000.00);
    expect(round($fees['partner_earning'], 2))->toBe(450000.00);
});

test('payment service get callout fee returns 30000', function () {
    $service = new PaymentService;

    expect($service->getCalloutFee())->toBe(30000.00);
});
