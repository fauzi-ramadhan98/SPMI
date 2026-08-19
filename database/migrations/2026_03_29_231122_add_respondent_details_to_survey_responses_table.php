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
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('respondent_id_number')->nullable()->after('respondent_type')->comment('NIM or NIDN');
            $table->string('company_name')->nullable()->after('respondent_id_number')->comment('For Mitra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['respondent_id_number', 'company_name']);
        });
    }
};
