<?php

use App\Models\Partner;
use App\Models\WalletBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('partner can get wallet balance', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    WalletBalance::create([
        'user_id' => $partner->user->id,
        'balance' => 150000,
        'frozen' => 0,
        'total_income' => 200000,
        'total_withdrawn' => 50000,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wallet');

    $response->assertStatus(200)
        ->assertJsonPath('data.balance', 150000);
});

test('partner gets zero balance if no wallet record', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wallet');

    // WalletService::getBalance() uses firstOrCreate — creates wallet with 0 balance
    $response->assertSuccessful()
        ->assertJsonPath('data.balance', 0);
});

test('partner can get wallet transactions', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wallet/transactions');

    $response->assertStatus(200);
});

test('partner can request withdrawal', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    WalletBalance::create([
        'user_id' => $partner->user->id,
        'balance' => 200000,
        'frozen' => 0,
        'total_income' => 200000,
        'total_withdrawn' => 0,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wallet/withdraw', [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => $partner->user->name,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'withdraw' => ['id', 'amount', 'status'],
        ]);

    $this->assertDatabaseHas('withdraw_requests', [
        'user_id' => $partner->user->id,
        'amount' => 50000,
        'bank_name' => 'BCA',
    ]);
});

test('withdrawal fails with insufficient balance', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    WalletBalance::create([
        'user_id' => $partner->user->id,
        'balance' => 10000,
        'frozen' => 0,
        'total_income' => 10000,
        'total_withdrawn' => 0,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wallet/withdraw', [
            'amount' => 50000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Test',
        ]);

    $response->assertStatus(422);
});

test('withdrawal requires minimum amount', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    WalletBalance::create([
        'user_id' => $partner->user->id,
        'balance' => 200000,
        'frozen' => 0,
        'total_income' => 200000,
        'total_withdrawn' => 0,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wallet/withdraw', [
            'amount' => 5000,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Test',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount']);
});

test('withdrawal requires all fields', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/wallet/withdraw', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['amount', 'bank_name', 'bank_account_number', 'bank_account_name']);
});

test('partner can get withdraw history', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/wallet/withdraw/history');

    $response->assertStatus(200);
});
