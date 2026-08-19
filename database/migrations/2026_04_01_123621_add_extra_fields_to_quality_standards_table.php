<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->string('kode_standar')->nullable()->after('id');
            $table->text('pernyataan_standar')->nullable()->after('kode_standar');
            $table->string('rujukan')->nullable()->after('pernyataan_standar');
            // file upload for uploaded standard documents
            $table->string('file_path')->nullable()->after('is_active');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('file_type')->nullable()->after('file_name'); // pdf/word/excel
            $table->unsignedBigInteger('file_size')->nullable()->after('file_type');
        });
    }

    public function down(): void
    {
        Schema::table('quality_standards', function (Blueprint $table) {
            $table->dropColumn(['kode_standar', 'pernyataan_standar', 'rujukan', 'file_path', 'file_name', 'file_type', 'file_size']);
        });
    }
};
