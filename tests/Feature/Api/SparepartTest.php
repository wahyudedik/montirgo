<?php

use App\Models\Partner;
use App\Models\Sparepart;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('partner can list their spareparts', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    Sparepart::factory()->count(3)->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/spareparts');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('partner can filter spareparts by category', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    Sparepart::factory()->count(2)->create(['partner_id' => $partner->id, 'category' => 'tire']);
    Sparepart::factory()->count(3)->create(['partner_id' => $partner->id, 'category' => 'brake']);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/spareparts?category=tire');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('partner can create a sparepart', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/spareparts', [
            'name' => 'Ban Motor Michelin',
            'description' => 'Ban motor Michelin 70/90-14',
            'category' => 'tire',
            'price' => 150000,
            'stock' => 10,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'data' => ['id', 'name', 'price', 'stock']]);

    $this->assertDatabaseHas('spareparts', [
        'partner_id' => $partner->id,
        'name' => 'Ban Motor Michelin',
        'price' => 150000,
        'stock' => 10,
    ]);
});

test('partner cannot create sparepart without required fields', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/spareparts', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'category', 'price', 'stock']);
});

test('partner can view sparepart detail', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $sparepart = Sparepart::factory()->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/partner/spareparts/{$sparepart->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $sparepart->id);
});

test('partner cannot view other partner sparepart', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $otherPartner = Partner::factory()->create();
    $sparepart = Sparepart::factory()->create(['partner_id' => $otherPartner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/partner/spareparts/{$sparepart->id}");

    $response->assertStatus(403);
});

test('partner can update sparepart', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $sparepart = Sparepart::factory()->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/spareparts/{$sparepart->id}", [
            'name' => 'Ban Motor Michelin Updated',
            'category' => 'tire',
            'price' => 175000,
            'stock' => 15,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Ban Motor Michelin Updated');

    $this->assertDatabaseHas('spareparts', [
        'id' => $sparepart->id,
        'name' => 'Ban Motor Michelin Updated',
        'price' => 175000,
    ]);
});

test('partner can delete sparepart', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $sparepart = Sparepart::factory()->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/partner/spareparts/{$sparepart->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('spareparts', ['id' => $sparepart->id]);
});

test('partner can toggle sparepart active status', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $sparepart = Sparepart::factory()->create(['partner_id' => $partner->id, 'is_active' => true]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/spareparts/{$sparepart->id}/toggle");

    $response->assertStatus(200)
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('spareparts', [
        'id' => $sparepart->id,
        'is_active' => false,
    ]);
});

test('unauthenticated user cannot access sparepart routes', function () {
    $response = $this->getJson('/api/v1/partner/spareparts');
    $response->assertStatus(401);
});
