<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Poin catatan client: skor Evaluasi Diri skala 0-4 (form mengizinkan
     * koma, mis. 3.5) — kolom tinyint sebelumnya membulatkan 3.5 menjadi 4.
     */
    public function up(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->decimal('score', 4, 1)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('evaluation_items', function (Blueprint $table) {
            $table->tinyInteger('score')->nullable()->change();
        });
    }
};
