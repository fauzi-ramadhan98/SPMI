<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\Unit;
use App\Models\QualityStandard;
use App\Models\Evaluation;
use App\Models\EvaluationItem;
use Illuminate\Database\Seeder;

/**
 * Contoh data Evaluasi Diri (ED) yang diisi SPMI untuk target Prodi & Unit.
 * Model akses: SPMI mengisi; role Prodi & Auditor hanya melihat.
 */
class EvaluationSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::whereHas('roles', fn($q) => $q->where('name', 'spmi'))->first()
            ?? User::first();

        $standards = QualityStandard::where('is_active', true)->orderBy('kode_standar')->get();

        $targets = [
            [AcademicProgram::class, 1, 'S1 Teknik Informatika', 'Ganjil', 2],
            [Unit::class, 7, 'Teknologi Informasi & Komunikasi', 'Tahunan', 3],
        ];

        foreach ($targets as [$type, $id, $label, $semester, $score]) {
            if (Evaluation::where('evaluable_type', $type)->where('evaluable_id', $id)->exists()) {
                continue;
            }

            $evaluation = Evaluation::create([
                'name' => 'Evaluasi Diri ' . $label . ' 2025/2026',
                'academic_year' => '2025/2026',
                'semester' => $semester,
                'evaluable_type' => $type,
                'evaluable_id' => $id,
                'status' => 'submitted',
                'created_by' => $creator->id,
            ]);

            foreach ($standards as $i => $std) {
                $sc = $score + (($i % 2) ? 1 : 0); // variasi skor agar realistis
                EvaluationItem::create([
                    'evaluation_id' => $evaluation->id,
                    'quality_standard_id' => $std->id,
                    'criteria' => $std->description ?? $std->name,
                    'indicator' => 'Ketercapaiuan indikator ' . $std->kode_standar,
                    'score' => min(4, $sc),
                    'narasi' => 'Evaluasi diri ' . $std->kode_standar . ' untuk ' . $label . ': pencapaian sudah cukup baik, '
                        . 'terdapat beberapa area yang perlu ditingkatkan pada aspek implementasi dan dokumentasi.',
                    'notes' => null,
                ]);
            }
        }
    }
}