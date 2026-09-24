<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // P5.2 — SK ditandai kategori: penetapan (P1) vs perubahan (hasil revisi standar)
        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->enum('kategori', ['penetapan', 'perubahan'])->default('penetapan')->after('jenis');
        });

        // P5.1 — 'aktif' = isi sesuai SK terakhir; 'draft_revisi' = sudah diedit, menunggu SK Perubahan ditandatangani
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->enum('revisi_status', ['aktif', 'draft_revisi'])->default('aktif')->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->dropColumn('revisi_status');
        });

        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
