<?php

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer can list their vehicles', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    Vehicle::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/vehicles');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('customer can create a vehicle', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/vehicles', [
            'brand' => 'Honda',
            'model' => 'Vario',
            'year' => 2023,
            'color' => 'Hitam',
            'license_plate' => 'AB 1234 CD',
            'type' => 'motorcycle',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'vehicle' => ['id', 'brand', 'model', 'license_plate']]);

    $this->assertDatabaseHas('vehicles', [
        'user_id' => $user->id,
        'brand' => 'Honda',
        'license_plate' => 'AB 1234 CD',
    ]);
});

test('first vehicle is automatically set as default', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/vehicles', [
            'brand' => 'Honda',
            'model' => 'Vario',
            'year' => 2023,
            'license_plate' => 'AB 1234 CD',
            'type' => 'motorcycle',
        ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('vehicles', [
        'user_id' => $user->id,
        'is_default' => true,
    ]);
});

test('customer cannot create vehicle without required fields', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/vehicles', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['brand', 'model', 'year', 'license_plate', 'type']);
});

test('customer can show a vehicle', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/vehicles/{$vehicle->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $vehicle->id);
});

test('customer cannot show another user vehicle', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle = Vehicle::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/vehicles/{$vehicle->id}");

    $response->assertStatus(403);
});

test('customer can update a vehicle', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/vehicles/{$vehicle->id}", [
            'color' => 'Merah',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('vehicle.color', 'Merah');
});

test('customer can delete a vehicle', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle = Vehicle::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson("/api/v1/vehicles/{$vehicle->id}");

    $response->assertStatus(200);
    $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
});

test('customer can set default vehicle', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;
    $vehicle1 = Vehicle::factory()->create(['user_id' => $user->id, 'is_default' => true]);
    $vehicle2 = Vehicle::factory()->create(['user_id' => $user->id, 'is_default' => false]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/vehicles/{$vehicle2->id}/default");

    $response->assertStatus(200);

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle2->id, 'is_default' => true]);
    $this->assertDatabaseHas('vehicles', ['id' => $vehicle1->id, 'is_default' => false]);
});

test('unauthenticated user cannot access vehicles', function () {
    $this->getJson('/api/v1/vehicles')
        ->assertStatus(401);
});
