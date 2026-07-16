<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->string('photo_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('partner_id');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spareparts');
    }
};
