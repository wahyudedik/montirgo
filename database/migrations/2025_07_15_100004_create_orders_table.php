<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type');
            $table->text('problem_description')->nullable();
            $table->json('photo_urls')->nullable();
            $table->decimal('location_lat', 10, 7);
            $table->decimal('location_lng', 10, 7);
            $table->text('location_address')->nullable();
            $table->enum('status', [
                'pending', 'dispatching', 'accepted', 'rejected',
                'on_the_way', 'arrived', 'in_progress', 'completed',
                'cancelled', 'expired',
            ])->default('pending');
            $table->decimal('callout_fee', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('platform_commission', 12, 2)->default(0);
            $table->decimal('partner_earning', 12, 2)->default(0);
            $table->enum('payment_method', ['cash', 'wallet', 'qris', 'card'])->default('cash');
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'refunded'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->enum('cancelled_by', ['user', 'partner', 'admin', 'system'])->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('dispatch_started_at')->nullable();
            $table->integer('dispatch_escalation')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index(['partner_id', 'status']);
            $table->index(['location_lat', 'location_lng']);
            $table->index('dispatch_started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
