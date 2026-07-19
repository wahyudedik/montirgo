<?php

use App\Models\Partner;
use App\Models\User;
use App\Services\LocationTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('partner can get their profile', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/profile');

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $partner->id)
        ->assertJsonPath('data.workshop_name', $partner->workshop_name);
});

test('customer without partner cannot access partner profile', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/profile');

    $response->assertStatus(404);
});

test('partner can update their profile', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/partner/profile', [
            'workshop_name' => 'Bengkel Baru Update',
            'description' => 'Deskripsi baru',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('partner.workshop_name', 'Bengkel Baru Update')
        ->assertJsonPath('partner.description', 'Deskripsi baru');

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
        'workshop_name' => 'Bengkel Baru Update',
        'description' => 'Deskripsi baru',
    ]);
});

test('partner can toggle online status', function () {
    $partner = Partner::factory()->create([
        'is_online' => false,
        'partner_status' => 'offline',
        'operational_schedule' => null,
    ]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $this->mock(LocationTrackingService::class, function ($mock) {
        $mock->shouldReceive('updatePartnerLocation')->once();
    });

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-online', [
            'current_lat' => -6.150000,
            'current_lng' => 106.800000,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'is_online' => true,
        ]);

    $this->assertDatabaseHas('partners', [
        'id' => $partner->id,
        'is_online' => true,
    ]);
});

test('partner can toggle availability', function () {
    $partner = Partner::factory()->create(['is_available' => true]);
    $token = $partner->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/toggle-availability');

    $response->assertStatus(200)
        ->assertJson([
            'is_available' => false,
        ]);
});

test('partner can update location', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    $this->mock(LocationTrackingService::class, function ($mock) {
        $mock->shouldReceive('updatePartnerLocation')->once();
    });

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/partner/location', [
            'lat' => '-6.1500000',
            'lng' => '106.8000000',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Lokasi berhasil diperbarui',
            'location' => [
                'lat' => '-6.1500000',
                'lng' => '106.8000000',
            ],
        ]);
});

test('nearby partners endpoint requires lat and lng', function () {
    $response = $this->getJson('/api/v1/partners/nearby');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lat', 'lng']);
});

test('unauthenticated user cannot access partner profile', function () {
    $response = $this->getJson('/api/v1/partner/profile');

    $response->assertStatus(401);
});
