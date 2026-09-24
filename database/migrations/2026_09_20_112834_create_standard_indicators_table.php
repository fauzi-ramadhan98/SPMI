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
        Schema::create('standard_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_standard_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('iku'); // iku | ikt
            $table->text('text');
            $table->text('target')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standard_indicators');
    }
};
