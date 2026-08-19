<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->foreignId('audit_cycle_id')->nullable()->after('deskripsi')
                ->constrained('audit_cycles')->nullOnDelete();
            $table->text('nama_standar')->nullable()->after('audit_cycle_id');
            $table->string('lokasi')->nullable()->default('Bandung')->after('nama_standar');
            $table->date('tanggal_sk')->nullable()->after('lokasi');
            $table->string('kop_path')->nullable()->after('file_size');
            $table->string('signature_path')->nullable()->after('kop_path');
        });
    }

    public function down(): void
    {
        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->dropForeign(['audit_cycle_id']);
            $table->dropColumn(['audit_cycle_id', 'nama_standar', 'lokasi', 'tanggal_sk', 'kop_path', 'signature_path']);
        });
    }
};