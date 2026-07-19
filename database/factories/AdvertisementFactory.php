<?php

namespace Database\Factories;

use App\Models\Advertisement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Advertisement>
 */
class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image_path' => 'advertisements/'.fake()->uuid().'.jpg',
            'target_url' => fake()->url(),
            'position' => fake()->randomElement(['sidebar', 'feed', 'popup', 'banner']),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
            'impressions' => 0,
            'clicks' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
