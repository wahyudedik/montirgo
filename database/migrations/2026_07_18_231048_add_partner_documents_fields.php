<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            // Task 4: Lengkapi dokumen partner
            $table->string('owner_name')->nullable()->after('workshop_name');
            $table->string('owner_phone', 20)->nullable()->after('owner_name');
            $table->string('selfie_with_ktp')->nullable()->after('ktp_photo');
            $table->string('front_workshop_photo')->nullable()->after('workshop_photo');
            $table->string('inside_workshop_photo')->nullable()->after('front_workshop_photo');
            $table->string('bank_name')->nullable()->after('business_license');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->string('npwp')->nullable()->after('bank_account_name');
            $table->string('nib')->nullable()->after('npwp');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name', 'owner_phone', 'selfie_with_ktp',
                'front_workshop_photo', 'inside_workshop_photo',
                'bank_name', 'bank_account_number', 'bank_account_name',
                'npwp', 'nib',
            ]);
        });
    }
};
