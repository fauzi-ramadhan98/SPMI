<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dupes = DB::table('audit_cycles')
            ->select('academic_year', 'semester', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('academic_year', 'semester')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($dupes as $d) {
            DB::table('audit_cycles')
                ->where('academic_year', $d->academic_year)
                ->where('semester', $d->semester)
                ->where('id', '<>', $d->keep_id)
                ->delete();
        }

        Schema::table('audit_cycles', function (Blueprint $table) {
            $table->unique(['academic_year', 'semester'], 'audit_cycles_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('audit_cycles', function (Blueprint $table) {
            $table->dropUnique('audit_cycles_period_unique');
        });
    }
};
