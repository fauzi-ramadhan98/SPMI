<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->enum('jenis', ['standar', 'auditor'])->default('standar')->after('judul');
        });

        DB::table('standard_decrees')
            ->where('judul', 'LIKE', '%AUDITOR%')
            ->update(['jenis' => 'auditor']);
    }

    public function down(): void
    {
        Schema::table('standard_decrees', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
