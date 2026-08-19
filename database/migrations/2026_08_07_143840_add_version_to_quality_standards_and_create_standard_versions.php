<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('is_active');
        });

        Schema::create('standard_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quality_standard_id');
            $table->unsignedInteger('version');
            $table->json('snapshot')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamps();

            $table->foreign('quality_standard_id')->references('id')->on('quality_standards')->cascadeOnDelete();
            $table->index(['quality_standard_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_versions');
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};