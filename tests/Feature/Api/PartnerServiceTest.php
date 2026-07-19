<?php

use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('partner can list their services', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    PartnerService::factory()->count(3)->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/services');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('partner can create a service', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/services', [
            'service_name' => 'Ganti Ban',
            'description' => 'Ganti ban depan atau belakang',
            'base_price' => 150000,
            'category' => 'tire',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'service' => ['id', 'service_name', 'base_price']]);

    $this->assertDatabaseHas('partner_services', [
        'partner_id' => $partner->id,
        'service_name' => 'Ganti Ban',
        'base_price' => 150000,
    ]);
});

test('partner cannot create service without required fields', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/services', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['service_name', 'base_price']);
});

test('partner can update a service', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;
    $service = PartnerService::factory()->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/services/{$service->id}", [
            'base_price' => 200000,
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('service.base_price', '200000.00');

    $this->assertDatabaseHas('partner_services', [
        'id' => $service->id,
        'base_price' => 200000,
    ]);
});

test('partner can delete a service', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;
    $service = PartnerService::factory()->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/partner/services/{$service->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('partner_services', ['id' => $service->id]);
});

test('partner can toggle service active status', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;
    $service = PartnerService::factory()->create(['partner_id' => $partner->id, 'is_active' => true]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/services/{$service->id}/toggle");

    $response->assertStatus(200)
        ->assertJsonPath('is_active', false);

    $this->assertDatabaseHas('partner_services', [
        'id' => $service->id,
        'is_active' => false,
    ]);
});

test('partner cannot update another partner service', function () {
    $partner1 = Partner::factory()->create();
    $partner2 = Partner::factory()->create();
    $token = $partner1->user->createToken('test')->plainTextToken;
    $service = PartnerService::factory()->create(['partner_id' => $partner2->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/partner/services/{$service->id}", [
            'base_price' => 999999,
        ]);

    $response->assertStatus(403);
});

test('customer cannot access partner services', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/services');

    $response->assertStatus(404);
});

test('unauthenticated user cannot access partner services', function () {
    $this->getJson('/api/v1/partner/services')
        ->assertStatus(401);
});
