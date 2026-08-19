<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->foreignId('standard_decree_id')->nullable()->after('version')->constrained('standard_decrees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('standard_decree_id');
        });
    }
};