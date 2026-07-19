<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerService>
 */
class PartnerServiceFactory extends Factory
{
    protected $model = PartnerService::class;

    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'service_name' => fake()->randomElement([
                'Ganti Ban', 'Servis Rem', 'Tune Up Mesin', 'Ganti Oli',
                'Service Kelistrikan', 'Overhaul Mesin', 'Servis Kopling',
            ]),
            'description' => fake()->sentence(),
            'base_price' => fake()->numberBetween(50000, 500000),
            'category' => fake()->randomElement(['tire', 'brake', 'engine', 'electrical', 'other']),
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
