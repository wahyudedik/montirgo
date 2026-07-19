<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->enum('vehicle_category', ['motorcycle', 'car']);
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('category', 30); // engine, electrical, tire, battery, fuel, ac, other
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['vehicle_category', 'is_active']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('symptoms');
    }
};
