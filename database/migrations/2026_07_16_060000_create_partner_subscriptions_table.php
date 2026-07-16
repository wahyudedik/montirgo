<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->enum('plan', ['basic', 'pro', 'enterprise'])->default('basic');
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->json('features')->nullable();
            $table->timestamps();

            $table->index('partner_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_subscriptions');
    }
};
