<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quality_standards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['IKU', 'IKT'])->default('IKU');
            $table->text('description')->nullable();
            $table->string('target_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_standards');
    }
};
