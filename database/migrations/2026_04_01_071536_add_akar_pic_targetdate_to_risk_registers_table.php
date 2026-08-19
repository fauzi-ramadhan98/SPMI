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
            $table->text('akar_masalah')->nullable()->after('temuan');       // Akar Masalah — setelah Temuan
            $table->string('pic')->nullable()->after('mitigation_plan');     // PIC — setelah Mitigasi
            $table->date('target_date')->nullable()->after('pic');           // Tanggal Penyelesaian
        });
    }

    public function down(): void
    {
        Schema::table('risk_registers', function (Blueprint $table) {
            $table->dropColumn(['akar_masalah', 'pic', 'target_date']);
        });
    }
};
