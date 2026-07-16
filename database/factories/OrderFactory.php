<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $calloutFee = fake()->randomElement([15000, 20000, 25000, 30000]);
        $serviceFee = fake()->numberBetween(50000, 500000);
        $totalAmount = $calloutFee + $serviceFee;
        $platformCommission = $calloutFee * 0.2 + ($serviceFee > 0 ? $serviceFee * fake()->randomElement([0.05, 0.08, 0.10]) : 0);

        return [
            'user_id' => User::factory()->customer(),
            'partner_id' => Partner::factory(),
            'service_type' => fake()->randomElement([
                'Engine Repair', 'Electrical Fix', 'Tire Change', 'Battery Replacement',
                'Oil Change', 'Brake Service', 'Transmission Repair', 'General Check',
            ]),
            'problem_description' => fake()->sentence(),
            'location_lat' => fake()->latitude(-6.2, -6.1),
            'location_lng' => fake()->longitude(106.7, 106.9),
            'location_address' => fake()->address(),
            'status' => fake()->randomElement(['pending', 'dispatching', 'completed', 'cancelled']),
            'callout_fee' => $calloutFee,
            'service_fee' => $serviceFee,
            'total_amount' => $totalAmount,
            'platform_commission' => round($platformCommission, 2),
            'partner_earning' => $totalAmount - $platformCommission,
            'payment_method' => fake()->randomElement(['cash', 'wallet', 'qris']),
            'payment_status' => fake()->randomElement(['unpaid', 'paid']),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'payment_status' => 'paid',
            'paid_at' => now(),
            'completed_at' => now(),
            'started_at' => now()->subHour(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'dispatch_started_at' => now(),
        ]);
    }
}
