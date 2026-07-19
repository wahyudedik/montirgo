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
        $calloutFee = 30000.00;
        $serviceFee = fake()->numberBetween(50000, 500000);
        $totalAmount = $calloutFee + $serviceFee;
        $commissionPercent = fake()->randomElement([5, 7, 10]);
        $platformCommission = $serviceFee * ($commissionPercent / 100);

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
            'partner_earning' => $totalAmount - round($platformCommission, 2),
            'payment_method' => fake()->randomElement(['qris', 'ewallet', 'bank_transfer']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
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
