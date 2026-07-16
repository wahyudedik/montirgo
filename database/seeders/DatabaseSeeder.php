<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Partner;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WalletBalance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── Admin ──────────────────────────────────────
        User::factory()->admin()->create([
            'name' => 'Admin MontirGo',
            'email' => 'admin@montirgo.id',
        ]);

        // ─── Customers (5) ─────────────────────────────
        $customers = User::factory()->customer()->count(5)->create();
        foreach ($customers as $customer) {
            Vehicle::factory()->create(['user_id' => $customer->id]);
            WalletBalance::create([
                'user_id' => $customer->id,
                'balance' => fake()->randomNumber(5, true) * 100,
            ]);
        }

        // ─── Partners (5) ──────────────────────────────
        $partners = Partner::factory()->count(5)->create();
        foreach ($partners as $partner) {
            // Create wallet for partner user
            WalletBalance::create([
                'user_id' => $partner->user_id,
                'balance' => fake()->numberBetween(100000, 2000000),
                'total_income' => fake()->numberBetween(500000, 5000000),
            ]);

            // Create services for each partner
            $services = [
                ['service_name' => 'Engine Repair', 'category' => 'engine', 'base_price' => 150000],
                ['service_name' => 'Electrical Fix', 'category' => 'electrical', 'base_price' => 100000],
                ['service_name' => 'Tire Change', 'category' => 'tire', 'base_price' => 50000],
                ['service_name' => 'Battery Replacement', 'category' => 'battery', 'base_price' => 80000],
                ['service_name' => 'Oil Change', 'category' => 'oil', 'base_price' => 60000],
            ];
            foreach ($services as $service) {
                $partner->services()->create($service);
            }
        }

        // ─── Pending Partners (2) ──────────────────────
        Partner::factory()->pending()->count(2)->create();

        // ─── Orders (20) ───────────────────────────────
        Order::factory()->count(20)->create();
    }
}
