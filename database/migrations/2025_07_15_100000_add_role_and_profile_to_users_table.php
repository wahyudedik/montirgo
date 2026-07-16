<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['customer', 'partner', 'admin'])->default('customer')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('avatar')->nullable()->after('phone');
            $table->decimal('location_lat', 10, 7)->nullable()->after('avatar');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            $table->boolean('is_active')->default(true)->after('location_lng');
            $table->timestamp('last_active_at')->nullable()->after('is_active');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'avatar',
                'location_lat',
                'location_lng',
                'is_active',
                'last_active_at',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
