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
        Schema::table('audit_instruments', function (Blueprint $table) {
            $table->string('finding_category', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_instruments', function (Blueprint $table) {
            $table->enum('finding_category', ['KTS', 'OB', 'Sesuai'])->nullable()->change();
        });
    }
};
