<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // Task 1.1: Kategori bengkel — motor vs mobil vs both
            $table->enum('workshop_category', ['motorcycle', 'car', 'both'])->default('both')->after('workshop_lng');

            // Task 1.3: Radius pelayanan per partner (km)
            $table->integer('service_radius')->default(30)->after('workshop_lng');

            // Task 2.3: Status partner granular
            $table->string('partner_status', 20)->default('offline')->after('is_available');

            // Task 3.2: Ubah enum status — tambah draft & rejected
            // Note: Kita gunakan string biasa bukan enum agar fleksibel
            $table->string('status', 20)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['workshop_category', 'service_radius', 'partner_status']);
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending')->change();
        });
    }
};
