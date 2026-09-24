<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * criteria menyimpan pernyataan standar (bisa > 255 karakter) dan indicator
     * menyimpan butir Daftar Tilik dengan awalan kode — varchar(255) memicu
     * SQLSTATE[22001] Data too long saat generate ED dari Daftar Tilik.
     * audit_findings.criteria juga dinaikkan karena validasi input-nya tanpa max.
     */
    public function up(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->text('criteria')->nullable()->change();
            $table->text('indicator')->nullable()->change();
        });

        Schema::table('audit_findings', function (Blueprint $table) {
            $table->text('criteria')->change();
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->string('criteria')->nullable()->change();
            $table->string('indicator')->nullable()->change();
        });

        Schema::table('audit_findings', function (Blueprint $table) {
            $table->string('criteria')->change();
        });
    }
};
