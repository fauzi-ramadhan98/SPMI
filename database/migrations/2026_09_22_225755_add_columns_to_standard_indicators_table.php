<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Kolom yang sama sudah dibuat oleh 2026_09_20_112834_create_standard_indicators_table,
     * jadi hanya dijalankan untuk kolom yang belum ada (idempoten).
     */
    public function up(): void
    {
        $hasQualityStandard = Schema::hasColumn('standard_indicators', 'quality_standard_id');
        $hasType = Schema::hasColumn('standard_indicators', 'type');
        $hasText = Schema::hasColumn('standard_indicators', 'text');
        $hasTarget = Schema::hasColumn('standard_indicators', 'target');
        $hasSortOrder = Schema::hasColumn('standard_indicators', 'sort_order');

        if ($hasQualityStandard && $hasType && $hasText && $hasTarget && $hasSortOrder) {
            return;
        }

        Schema::table('standard_indicators', function (Blueprint $table) use ($hasQualityStandard, $hasType, $hasText, $hasTarget, $hasSortOrder) {
            if (!$hasQualityStandard) {
                $table->foreignId('quality_standard_id')->constrained()->cascadeOnDelete()->after('id');
            }
            if (!$hasType) {
                $table->string('type')->default('iku')->after('quality_standard_id');
            }
            if (!$hasText) {
                $table->text('text')->after('type');
            }
            if (!$hasTarget) {
                $table->text('target')->nullable()->after('text');
            }
            if (!$hasSortOrder) {
                $table->unsignedInteger('sort_order')->default(0)->after('target');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('standard_indicators', 'quality_standard_id')) {
            Schema::table('standard_indicators', function (Blueprint $table) {
                $table->dropForeign(['quality_standard_id']);
            });
        }

        $columns = array_filter(
            ['quality_standard_id', 'type', 'text', 'target', 'sort_order'],
            fn ($column) => Schema::hasColumn('standard_indicators', $column)
        );

        if ($columns) {
            Schema::table('standard_indicators', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
