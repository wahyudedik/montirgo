<?php

use App\Models\Mechanic;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('partner can list their mechanics', function () {
    $partner = Partner::factory()->create();
    Mechanic::factory()->count(3)->create(['partner_id' => $partner->id]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/mechanics');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('partner can add a mechanic', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/mechanics', [
            'name' => 'Budi Mekanik',
            'phone' => '081234567890',
            'expertise' => 'motorcycle',
            'description' => 'Ahli mesin motor',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('mechanic.name', 'Budi Mekanik')
        ->assertJsonPath('mechanic.expertise', 'motorcycle');

    $this->assertDatabaseHas('mechanics', [
        'partner_id' => $partner->id,
        'name' => 'Budi Mekanik',
    ]);
});

test('partner can update a mechanic', function () {
    $partner = Partner::factory()->create();
    $mechanic = Mechanic::factory()->create(['partner_id' => $partner->id]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/mechanics/{$mechanic->id}", [
            'name' => 'Budi Updated',
            'is_active' => false,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('mechanic.name', 'Budi Updated')
        ->assertJsonPath('mechanic.is_active', false);
});

test('partner can delete a mechanic', function () {
    $partner = Partner::factory()->create();
    $mechanic = Mechanic::factory()->create(['partner_id' => $partner->id]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/partner/mechanics/{$mechanic->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Mekanik berhasil dihapus');

    $this->assertDatabaseMissing('mechanics', ['id' => $mechanic->id]);
});

test('partner cannot update another partner mechanic', function () {
    $partner1 = Partner::factory()->create();
    $partner2 = Partner::factory()->create();
    $mechanic = Mechanic::factory()->create(['partner_id' => $partner2->id]);
    $token = $partner1->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/mechanics/{$mechanic->id}", [
            'name' => 'Hacked',
        ]);

    $response->assertStatus(403);
});

test('customer without partner cannot access mechanics', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/mechanics');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('adding mechanic requires name and expertise', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/mechanics', [
            'name' => '',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'expertise']);
});
