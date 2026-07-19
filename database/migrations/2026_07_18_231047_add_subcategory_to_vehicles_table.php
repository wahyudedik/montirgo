<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Subkategori kendaraan
            // Motor: matic, bebek, sport, electric
            // Mobil: manual, matic, diesel, hybrid, ev
            // Disimpan sebagai string agar fleksibel (bukan enum gabungan)
            $table->string('subcategory', 30)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('subcategory');
        });
    }
};
