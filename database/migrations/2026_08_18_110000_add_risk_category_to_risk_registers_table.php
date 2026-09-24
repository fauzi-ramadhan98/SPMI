<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->enum('risk_category', [
                'Operasional',
                'SDM',
                'Keuangan',
                'Teknologi',
                'Kepatuhan',
                'Reputasi',
            ])->nullable()->after('quality_standard_id');
        });
    }

    public function down(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn('risk_category');
        });
    }
};