<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            // Expand from VARCHAR(10) to VARCHAR(50) to support
            // accreditation values like 'Baik Sekali', 'Sangat Baik', 'Unggul', etc.
            $table->string('accreditation', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->string('accreditation', 10)->nullable()->change();
        });
    }
};
