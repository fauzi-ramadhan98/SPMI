<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->string('head_name')->nullable()->after('accreditation');
        });
    }

    public function down(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->dropColumn('head_name');
        });
    }
};
