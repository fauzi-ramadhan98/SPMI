<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remap finding_category lama ke skala 5 level (4/3/2/1/0) sesuai skor.
     */
    public function up(): void
    {
        $byScore = [
            '4' => 'Melampaui Standar Nasional',
            '3' => 'Sesuai dengan Standar',
            '2' => 'Tidak Tercapai Ringan (KTS Minor)',
            '1' => 'Tidak Tercapai Sedang (KTS Mayor)',
            '0' => 'Pelanggaran Fatal',
        ];

        $oldFallback = [
            'Sesuai / Efektif'            => 'Melampaui Standar Nasional',
            'Observasi'                   => 'Sesuai dengan Standar',
            'OB'                          => 'Sesuai dengan Standar',
            'Ketidaksesuaian (Minor)'     => 'Tidak Tercapai Ringan (KTS Minor)',
            'Ketidaksesuaian (Mayor)'     => 'Tidak Tercapai Sedang (KTS Mayor)',
            'KTS'                         => 'Tidak Tercapai Sedang (KTS Mayor)',
        ];

        DB::table('audit_instruments')->orderBy('id')->chunkById(200, function ($instruments) use ($byScore, $oldFallback) {
            foreach ($instruments as $inst) {
                $new = null;
                if ($inst->score !== null && array_key_exists((string) $inst->score, $byScore)) {
                    $new = $byScore[(string) $inst->score];
                } elseif ($inst->finding_category !== null && array_key_exists($inst->finding_category, $oldFallback)) {
                    $new = $oldFallback[$inst->finding_category];
                }

                if ($new !== null) {
                    DB::table('audit_instruments')->where('id', $inst->id)->update(['finding_category' => $new]);
                }
            }
        });
    }

    /**
     * Reverse — tidak dapat dikembalikan secara deterministik; kosongkan.
     */
    public function down(): void
    {
        DB::table('audit_instruments')->whereNotNull('finding_category')->update(['finding_category' => null]);
    }
};
