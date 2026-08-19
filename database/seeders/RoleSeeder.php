<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Struktur peran baru sesuai Permendiktisaintek No. 39 Tahun 2025.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'administrator' => 'Pengelola sistem: manajemen user, master data (prodi & unit), halaman publik',
            'spmi'          => 'LPM/SPMI: standar mutu, siklus AMI, alokasi auditor, survei, laporan',
            'pimpinan'      => 'Ketua & Wakil: persetujuan LHA/RTM, pemantauan capaian mutu',
            'auditor'       => 'Tim auditor: pengisian borang/instrumen, temuan, verifikasi tindak lanjut',
            'prodi'         => 'Program studi: profil risiko & dokumen prodi, tindak lanjut temuan',
            'unit'          => 'Unit kerja (administratif/akademik/penunjang): profil risiko & tindak lanjut',
            'guest'         => 'Pengunjung terdaftar (read-only)',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web']
            );
        }
    }
}
