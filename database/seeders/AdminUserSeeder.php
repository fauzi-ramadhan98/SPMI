<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\Unit;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat user contoh untuk setiap peran pada struktur baru
     * (Permendiktisaintek No. 39 Tahun 2025).
     */
    public function run(): void
    {
        $firstProgram = AcademicProgram::first();
        $firstUnit = Unit::first();

        // Administrator — pengelola sistem
        $administrator = User::firstOrCreate(
            ['email' => 'admin@spmi.ac.id'],
            ['name' => 'Administrator SPMI', 'password' => Hash::make('password')]
        );
        $administrator->syncRoles(['administrator']);

        // SPMI / LPM
        $spmi = User::firstOrCreate(
            ['email' => 'spmi@spmi.ac.id'],
            ['name' => 'Tim SPMI / LPM', 'password' => Hash::make('password')]
        );
        $spmi->syncRoles(['spmi']);

        // Pimpinan (Ketua)
        $ketua = User::firstOrCreate(
            ['email' => 'ketua@spmi.ac.id'],
            ['name' => 'Ketua STMIK Mardira Indonesia', 'password' => Hash::make('password'), 'pimpinan_level' => 'ketua']
        );
        $ketua->syncRoles(['pimpinan']);

        // Pimpinan (Wakil)
        $wakil = User::firstOrCreate(
            ['email' => 'wakil@spmi.ac.id'],
            ['name' => 'Wakil Ketua', 'password' => Hash::make('password'), 'pimpinan_level' => 'wakil']
        );
        $wakil->syncRoles(['pimpinan']);

        // Auditor
        $auditor = User::firstOrCreate(
            ['email' => 'auditor@spmi.ac.id'],
            ['name' => 'Auditor Internal', 'password' => Hash::make('password')]
        );
        $auditor->syncRoles(['auditor']);

        // Prodi
        $prodi = User::updateOrCreate(
            ['email' => 'ti@spmi.ac.id'],
            [
                'name' => 'Kaprodi Teknik Informatika',
                'password' => Hash::make('password'),
                'academic_program_id' => $firstProgram?->id,
            ]
        );
        $prodi->syncRoles(['prodi']);

        // Unit (contoh: Teknologi Informasi & Komunikasi)
        $tikUnit = Unit::where('code', 'TIK')->first() ?? Unit::first();
        $unit = User::updateOrCreate(
            ['email' => 'unit@spmi.ac.id'],
            [
                'name' => 'Kepala Unit Layanan',
                'password' => Hash::make('password'),
                'unit_id' => $tikUnit?->id,
            ]
        );
        $unit->syncRoles(['unit']);
    }
}
