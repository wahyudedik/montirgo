<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Field untuk wizard diagnosis — simpan gejala yang dipilih user
            $table->json('selected_symptoms')->nullable()->after('problem_description');

            // Field untuk menyimpan vehicle_category saat order dibuat
            // (agar dispatch tahu harus cari bengkel motor/mobil tanpa join ke vehicles)
            $table->enum('vehicle_category', ['motorcycle', 'car', 'other'])->nullable()->after('vehicle_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['selected_symptoms', 'vehicle_category']);
        });
    }
};
