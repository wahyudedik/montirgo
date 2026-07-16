<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('api_url')->nullable();
            $table->json('config')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index('code');
            $table->index('status');
        });

        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_partner_id')->constrained()->cascadeOnDelete();
            $table->string('claim_number')->nullable();
            $table->decimal('claimed_amount', 12, 2)->default(0);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->enum('status', ['submitted', 'processing', 'approved', 'rejected', 'paid'])->default('submitted');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('insurance_partner_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
        Schema::dropIfExists('insurance_partners');
    }
};
