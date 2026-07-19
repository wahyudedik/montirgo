<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Partner;
use App\Models\Sparepart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sparepart>
 */
class SparepartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(10),
            'category' => fake()->randomElement(['tire', 'brake', 'engine', 'electrical', 'oil', 'battery', 'general']),
            'price' => fake()->numberBetween(5000, 500000),
            'stock' => fake()->numberBetween(0, 50),
            'photo_url' => null,
            'is_active' => true,
        ];
    }

    /**
     * Sparepart yang tidak aktif.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Sparepart dengan stok habis.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
