<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can register as customer', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test Customer',
        'email' => 'customer@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '081234567890',
        'role' => 'customer',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email', 'role'],
            'token',
        ])
        ->assertJsonPath('user.role', 'customer');

    $this->assertDatabaseHas('users', [
        'email' => 'customer@test.com',
        'role' => 'customer',
    ]);
});

test('user can register as partner', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Bengkel Test',
        'email' => 'partner@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'partner',
    ]);

    $response->assertStatus(201);

    $user = User::where('email', 'partner@test.com')->first();
    $this->assertNotNull($user->partner);
    expect($user->partner->status)->toBe('draft');
});

test('admin registration is blocked via public API', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Hacker Admin',
        'email' => 'admin@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin',
    ]);

    // Validation rule 'in:customer,partner' rejects 'admin' with 422
    $response->assertStatus(422);
    $this->assertDatabaseMissing('users', ['email' => 'admin@test.com']);
});

test('registration requires valid data', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

test('registration rejects duplicate email', function () {
    User::factory()->create(['email' => 'existing@test.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Duplicate',
        'email' => 'existing@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'login@test.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@test.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'user' => ['id', 'email'],
            'token',
        ]);
});

test('login fails with wrong password', function () {
    User::factory()->create([
        'email' => 'login@test.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'login@test.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
});

test('login fails for inactive user', function () {
    User::factory()->create([
        'email' => 'inactive@test.com',
        'password' => Hash::make('secret123'),
        'is_active' => false,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'inactive@test.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(403);
});

test('user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200);
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('user can get profile', function () {
    $user = User::factory()->create(['name' => 'Profile User']);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/auth/profile');

    $response->assertStatus(200)
        ->assertJsonPath('user.name', 'Profile User');
});

test('user can update profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson('/api/v1/auth/profile', [
            'name' => 'Updated Name',
            'phone' => '089999999999',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'phone' => '089999999999',
    ]);
});

test('unauthenticated user cannot access profile', function () {
    $response = $this->getJson('/api/v1/auth/profile');

    $response->assertStatus(401);
});

test('user can update location', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/auth/location', [
            'location_lat' => -6.15,
            'location_lng' => 106.80,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'location_lat' => -6.15,
        'location_lng' => 106.80,
    ]);
});
