<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master daftar tilik (butir penilaian) per standar mutu.
     * Sumber generate instrumen audit (E-Kertas Kerja).
     */
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_standard_id')->constrained('quality_standards')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->text('indicator');
            $table->unsignedTinyInteger('max_score')->default(4);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
