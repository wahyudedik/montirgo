<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_services', function (Blueprint $table) {
            // Tambah vehicle_category agar bisa dibedakan layanan motor vs mobil
            $table->enum('vehicle_category', ['motorcycle', 'car', 'both'])->default('both')->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('partner_services', function (Blueprint $table) {
            $table->dropColumn('vehicle_category');
        });
    }
};
