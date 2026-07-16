<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('workshop_name');
            $table->string('workshop_address');
            $table->decimal('workshop_lat', 10, 7);
            $table->decimal('workshop_lng', 10, 7);
            $table->string('ktp_number', 30)->nullable();
            $table->string('ktp_photo')->nullable();
            $table->string('workshop_photo')->nullable();
            $table->string('business_license')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_available')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['workshop_lat', 'workshop_lng']);
            $table->index('is_online');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
