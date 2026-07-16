<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Add column if it doesn't exist
        if (! Schema::hasColumn('orders', 'code')) {
            Schema::table('orders', function ($table) {
                $table->string('code', 20)->nullable()->after('id');
            });
        }

        // Populate existing orders with unique codes
        $orders = DB::table('orders')->whereNull('code')->orWhere('code', '')->get();
        foreach ($orders as $order) {
            do {
                $code = 'MTG-'.strtoupper(Str::random(6));
            } while (DB::table('orders')->where('code', $code)->exists());

            DB::table('orders')->where('id', $order->id)->update(['code' => $code]);
        }

        // Add unique constraint if not already present (database-agnostic)
        if (! Schema::hasIndex('orders', 'orders_code_unique')) {
            Schema::table('orders', function ($table) {
                $table->string('code', 20)->nullable(false)->unique()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function ($table) {
            $table->dropColumn('code');
        });
    }
};
