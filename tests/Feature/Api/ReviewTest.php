<?php

use App\Models\Order;
use App\Models\Partner;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer can create a review', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $order = Order::factory()->completed()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/reviews', [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Servis sangat memuaskan!',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'review' => ['id', 'rating', 'comment']]);

    $this->assertDatabaseHas('reviews', [
        'order_id' => $order->id,
        'user_id' => $user->id,
        'rating' => 5,
    ]);
});

test('customer cannot review incomplete order', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $order = Order::factory()->pending()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/reviews', [
            'order_id' => $order->id,
            'rating' => 5,
        ]);

    $response->assertStatus(422);
});

test('customer cannot review with invalid rating', function () {
    $user = User::factory()->customer()->create();
    $partner = Partner::factory()->create();
    $order = Order::factory()->completed()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/reviews', [
            'order_id' => $order->id,
            'rating' => 6,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['rating']);
});

test('customer can list their reviews', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;
    $partner = Partner::factory()->create();

    Review::factory()->count(3)->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/reviews');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('partner can list reviews for their workshop', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;

    Review::factory()->count(2)->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/partner/reviews');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('partner can reply to a review', function () {
    $partner = Partner::factory()->create();
    $token = $partner->user->createToken('test')->plainTextToken;
    $review = Review::factory()->create(['partner_id' => $partner->id]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson("/api/v1/reviews/{$review->id}/reply", [
            'reply' => 'Terima kasih atas reviewnya!',
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('review.partner_reply', 'Terima kasih atas reviewnya!');

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'partner_reply' => 'Terima kasih atas reviewnya!',
    ]);
});

test('anyone can view partner reviews (public)', function () {
    $partner = Partner::factory()->create();
    Review::factory()->count(2)->create(['partner_id' => $partner->id]);

    $response = $this->getJson("/api/v1/partners/{$partner->id}/reviews");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

test('authenticated user can view review stats', function () {
    $user = User::factory()->customer()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/reviews/stats');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['total_reviews', 'avg_rating', 'distribution']]);
});

test('unauthenticated user cannot create review', function () {
    $this->postJson('/api/v1/reviews', [
        'order_id' => 1,
        'rating' => 5,
    ])->assertStatus(401);
});
