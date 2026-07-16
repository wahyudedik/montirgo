<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->partner(),
            'workshop_name' => fake()->company().' Bengkel',
            'workshop_address' => fake()->address(),
            'workshop_lat' => fake()->latitude(-6.2, -6.1),
            'workshop_lng' => fake()->longitude(106.7, 106.9),
            'status' => 'approved',
            'rating_avg' => fake()->randomFloat(2, 3.5, 5.0),
            'total_orders' => fake()->numberBetween(0, 100),
            'is_online' => fake()->boolean(60),
            'is_available' => true,
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
