<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicProgram;

class AcademicProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicProgram::updateOrCreate(['code' => 'S1TI'], [
            'name' => 'Teknik Informatika',
            'faculty' => 'STMIK Mardira Indonesia',
            'degree_level' => 'S1',
            'accreditation' => 'B',
            'head_name' => 'Dr Toni Kusnandar, M.T',
        ]);

        AcademicProgram::updateOrCreate(['code' => 'D3TI'], [
            'name' => 'Teknik Informatika',
            'faculty' => 'STMIK Mardira Indonesia',
            'degree_level' => 'D3',
            'accreditation' => 'B',
            'head_name' => 'Feri Alpiyasin, M.Kom',
        ]);

        AcademicProgram::updateOrCreate(['code' => 'D3MI'], [
            'name' => 'Manajemen Informatika',
            'faculty' => 'STMIK Mardira Indonesia',
            'degree_level' => 'D3',
            'accreditation' => 'B',
            'head_name' => 'Jajat Sudrajat',
        ]);

        AcademicProgram::updateOrCreate(['code' => 'D3KA'], [
            'name' => 'Komputerisasi Akuntansi',
            'faculty' => 'STMIK Mardira Indonesia',
            'degree_level' => 'D3',
            'accreditation' => 'B',
            'head_name' => 'Hasanah Tisna Amijaya, M.Ak',
        ]);
    }
}
