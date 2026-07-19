<?php

use App\Models\Advertisement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('anyone can list active advertisements', function () {
    Advertisement::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);
    Advertisement::factory()->create([
        'is_active' => false,
    ]);

    $response = $this->getJson('/api/v1/ads');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('anyone can list ads filtered by position', function () {
    Advertisement::factory()->create([
        'position' => 'banner',
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);
    Advertisement::factory()->create([
        'position' => 'feed',
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);

    $response = $this->getJson('/api/v1/ads?position=banner');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('anyone can view advertisement detail', function () {
    $ad = Advertisement::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(30),
    ]);

    $response = $this->getJson("/api/v1/ads/{$ad->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $ad->id)
        ->assertJsonPath('data.title', $ad->title);
});

test('anyone can track impression', function () {
    $ad = Advertisement::factory()->create(['impressions' => 0]);

    $response = $this->postJson("/api/v1/ads/{$ad->id}/impression");

    $response->assertStatus(200);

    $this->assertDatabaseHas('advertisements', [
        'id' => $ad->id,
        'impressions' => 1,
    ]);
});

test('anyone can track click', function () {
    $ad = Advertisement::factory()->create(['clicks' => 0]);

    $response = $this->postJson("/api/v1/ads/{$ad->id}/click");

    $response->assertStatus(200)
        ->assertJsonPath('target_url', $ad->target_url);

    $this->assertDatabaseHas('advertisements', [
        'id' => $ad->id,
        'clicks' => 1,
    ]);
});

test('expired ads are not returned', function () {
    Advertisement::factory()->create([
        'is_active' => true,
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(5),
    ]);

    $response = $this->getJson('/api/v1/ads');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});
