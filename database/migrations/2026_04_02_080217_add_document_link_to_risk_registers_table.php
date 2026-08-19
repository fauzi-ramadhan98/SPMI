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
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->text('document_link')->nullable()->after('mitigation_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn('document_link');
        });
    }
};
