<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed master tahun akademik.
     */
    public function run(): void
    {
        $years = ['2023/2024', '2024/2025', '2025/2026', '2026/2027'];

        foreach ($years as $name) {
            AcademicYear::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
