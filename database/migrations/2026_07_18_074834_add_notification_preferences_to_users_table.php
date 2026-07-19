<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('fcm_token');
        });

        // Set default preferences untuk user yang sudah ada
        DB::table('users')->whereNull('notification_preferences')->update([
            'notification_preferences' => json_encode([
                'push_enabled' => true,
                'chat' => true,
                'order_status' => true,
                'payment' => true,
                'new_order' => true,
            ]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
