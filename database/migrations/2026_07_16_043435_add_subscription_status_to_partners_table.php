<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('subscription_status')->default('basic')->after('status');
            $table->integer('total_reviews')->default(0)->after('total_orders');
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['subscription_status', 'total_reviews']);
        });
    }
};
