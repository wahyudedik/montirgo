<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Mechanic;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mechanic>
 */
class MechanicFactory extends Factory
{
    protected $model = Mechanic::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'name' => fake()->name(),
            'photo' => null,
            'phone' => fake()->phoneNumber(),
            'expertise' => fake()->randomElement(['motorcycle', 'car', 'both']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
