<?php

use App\Models\InsuranceClaim;
use App\Models\InsurancePartner;
use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can list active insurance partners', function () {
    InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    InsurancePartner::create([
        'name' => 'Asuransi B (inactive)',
        'code' => 'INS-B',
        'status' => 'inactive',
    ]);

    $response = $this->getJson('/api/v1/insurance/partners');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Asuransi A');
});

test('customer can create insurance claim for their order', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $partner = Partner::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'completed',
    ]);

    $insurancePartner = InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/insurance-claim", [
            'insurance_partner_id' => $insurancePartner->id,
            'claimed_amount' => 500000,
            'notes' => 'Klaim untuk kerusakan mesin',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'claim_number', 'status'],
        ])
        ->assertJsonPath('data.status', 'submitted');

    $this->assertDatabaseHas('insurance_claims', [
        'order_id' => $order->id,
        'insurance_partner_id' => $insurancePartner->id,
        'status' => 'submitted',
    ]);
});

test('partner can create insurance claim for assigned order', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $user = User::factory()->customer()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'completed',
    ]);

    $insurancePartner = InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/insurance-claim", [
            'insurance_partner_id' => $insurancePartner->id,
            'claimed_amount' => 300000,
        ]);

    $response->assertStatus(201);
});

test('unauthorized user cannot create insurance claim', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $order = Order::factory()->create([
        'status' => 'completed',
    ]);

    $insurancePartner = InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/insurance-claim", [
            'insurance_partner_id' => $insurancePartner->id,
            'claimed_amount' => 500000,
        ]);

    $response->assertStatus(403);
});

test('cannot create duplicate insurance claim', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $partner = Partner::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'completed',
    ]);

    $insurancePartner = InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    InsuranceClaim::create([
        'order_id' => $order->id,
        'insurance_partner_id' => $insurancePartner->id,
        'claim_number' => 'CLM-EXISTING',
        'claimed_amount' => 500000,
        'status' => 'submitted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/insurance-claim", [
            'insurance_partner_id' => $insurancePartner->id,
            'claimed_amount' => 500000,
        ]);

    $response->assertStatus(422);
});

test('customer can view claim status', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $partner = Partner::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    $insurancePartner = InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    $claim = InsuranceClaim::create([
        'order_id' => $order->id,
        'insurance_partner_id' => $insurancePartner->id,
        'claim_number' => 'CLM-TEST001',
        'claimed_amount' => 500000,
        'status' => 'submitted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/insurance-claims/{$claim->id}/status");

    $response->assertStatus(200)
        ->assertJsonPath('data.claim_number', 'CLM-TEST001')
        ->assertJsonPath('data.status', 'submitted');
});

test('unauthorized user cannot view claim status', function () {
    $user1 = User::factory()->customer()->create();
    $user2 = User::factory()->customer()->create();
    $token2 = $user2->createToken('test')->plainTextToken;

    $partner = Partner::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user1->id,
        'partner_id' => $partner->id,
    ]);

    $insurancePartner = InsurancePartner::create([
        'name' => 'Asuransi A',
        'code' => 'INS-A',
        'status' => 'active',
    ]);

    $claim = InsuranceClaim::create([
        'order_id' => $order->id,
        'insurance_partner_id' => $insurancePartner->id,
        'claim_number' => 'CLM-TEST002',
        'claimed_amount' => 500000,
        'status' => 'submitted',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token2}")
        ->getJson("/api/v1/insurance-claims/{$claim->id}/status");

    $response->assertStatus(403);
});

test('unauthenticated user cannot create insurance claim', function () {
    $order = Order::factory()->create(['status' => 'completed']);

    $response = $this->postJson("/api/v1/orders/{$order->id}/insurance-claim", [
        'insurance_partner_id' => 1,
        'claimed_amount' => 500000,
    ]);

    $response->assertStatus(401);
});

test('insurance claim requires valid data', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $partner = Partner::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'completed',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/orders/{$order->id}/insurance-claim", [
            'insurance_partner_id' => 99999,
            'claimed_amount' => -100,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['insurance_partner_id', 'claimed_amount']);
});
