<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: column type change is handled by modifying the column definition
            // SQLite uses dynamic typing, so ENUM is not enforced at DB level
            // The application code handles validation via 'in:qris,ewallet,bank_transfer'
            return;
        }

        // MySQL: Migrate existing data before altering ENUM constraints
        // wallet → ewallet, cash → qris (default fallback)
        DB::statement("UPDATE orders SET payment_method = 'ewallet' WHERE payment_method = 'wallet'");
        DB::statement("UPDATE orders SET payment_method = 'qris' WHERE payment_method = 'cash'");
        DB::statement("UPDATE payments SET method = 'ewallet' WHERE method = 'wallet'");
        DB::statement("UPDATE payments SET method = 'qris' WHERE method = 'cash'");

        // Now safely alter ENUM constraints
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('qris', 'ewallet', 'bank_transfer') DEFAULT 'qris'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('qris', 'ewallet', 'bank_transfer') NOT NULL");
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cash', 'wallet', 'qris', 'card') DEFAULT 'cash'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN method ENUM('cash', 'wallet', 'qris', 'card', 'bank_transfer') NOT NULL");
    }
};
