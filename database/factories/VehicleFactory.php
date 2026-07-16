<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        $brands = ['Honda', 'Yamaha', 'Suzuki', 'Kawasaki', 'Toyota', 'Daihatsu', 'Mitsubishi'];
        $brand = fake()->randomElement($brands);
        $types = ['motorcycle', 'car', 'other'];
        $type = $brand === 'Toyota' || $brand === 'Daihatsu' || $brand === 'Mitsubishi' ? 'car' : fake()->randomElement(['motorcycle', 'car']);

        return [
            'user_id' => User::factory()->customer(),
            'brand' => $brand,
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2015, 2025),
            'color' => fake()->safeColorName(),
            'license_plate' => strtoupper(fake()->bothify('?? #### ??')),
            'type' => $type,
            'is_default' => true,
        ];
    }
}
