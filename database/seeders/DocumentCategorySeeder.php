<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Master kategori dokumen — hanya kategori header/induk (level 1).
     * Sub-kategori (K-SKP, M-P1, dst.) sengaja tidak lagi di-seed; hierarki
     * sub-kategori dapat dibuat manual melalui menu Kategori Dokumen
     * (satu sub-kategori kini boleh punya sub-kategori lagi).
     */
    public function run(): void
    {
        $categories = [
            // Modul Dokumen Mutu (SPMI) - Kategori Header (Induk)
            ['name' => 'Kebijakan SPMI',              'code' => 'KEBIJAKAN', 'module' => 'dokumen_mutu', 'target_roles' => 'spmi'],
            ['name' => 'Manual SPMI (Manual PPEPP)',  'code' => 'MANUAL',    'module' => 'dokumen_mutu', 'target_roles' => 'spmi'],
            ['name' => 'Standar Mutu (SN-Dikti dan Standar Institusi)', 'code' => 'STANDAR', 'module' => 'dokumen_mutu', 'target_roles' => 'spmi'],
            ['name' => 'Formulir / SOP / Daftar Tilik', 'code' => 'FORM-SOP', 'module' => 'dokumen_mutu', 'target_roles' => 'spmi'],

            // Modul Surat Tugas / AMI (SPMI)
            ['name' => 'Surat Tugas Auditor',         'code' => 'SK-AUD',    'module' => 'surat_tugas',  'target_roles' => 'spmi'],
            ['name' => 'Jadwal Visitasi',             'code' => 'VISIT',     'module' => 'surat_tugas',  'target_roles' => 'spmi'],
            ['name' => 'Instrumen / Borang Audit',    'code' => 'BORANG',    'module' => 'surat_tugas',  'target_roles' => 'spmi'],

            // Panel RTM (SPMI upload; pimpinan lihat)
            ['name' => 'Notulen RTM',                 'code' => 'NOT-RTM',   'module' => 'rtm',          'target_roles' => 'spmi'],
            ['name' => 'Daftar Hadir RTM',            'code' => 'HADIR-RTM', 'module' => 'rtm',          'target_roles' => 'spmi'],
            ['name' => 'Dokumentasi RTM',             'code' => 'DOK-RTM',   'module' => 'rtm',          'target_roles' => 'spmi'],

            // Evaluasi Diri (Prodi / Unit)
            ['name' => 'Notulen Rapat Prodi',         'code' => 'NOT-PRODI', 'module' => 'evaluasi_diri','target_roles' => 'prodi'],
            ['name' => 'Daftar Hadir (Dosen/Mahasiswa)', 'code' => 'HADIR-PRODI', 'module' => 'evaluasi_diri','target_roles' => 'prodi'],
            ['name' => 'SK Mengajar / Pembimbing',    'code' => 'SK-MENGAJAR','module' => 'evaluasi_diri','target_roles' => 'prodi'],
            ['name' => 'Dokumentasi Kegiatan',        'code' => 'DOK-PRODI', 'module' => 'evaluasi_diri','target_roles' => 'prodi'],
            ['name' => 'SOP Layanan',                 'code' => 'SOP-LAYAN', 'module' => 'evaluasi_diri','target_roles' => 'unit'],
            ['name' => 'Notulen Rapat Divisi',        'code' => 'NOT-DIV',   'module' => 'evaluasi_diri','target_roles' => 'unit'],
            ['name' => 'Daftar Hadir Pelatihan Tendik','code' => 'HADIR-TENDIK', 'module' => 'evaluasi_diri','target_roles' => 'unit'],
            ['name' => 'Dokumentasi Fasilitas',       'code' => 'DOK-UNIT',  'module' => 'evaluasi_diri','target_roles' => 'unit'],
        ];

        // Hapus seluruh kategori lama (termasuk sub-kategori) agar seeder ini
        // menghasilkan struktur bersih yang hanya berisi header.
        DocumentCategory::whereIn('module', ['dokumen_mutu', 'surat_tugas', 'rtm', 'evaluasi_diri'])->delete();

        foreach ($categories as $cat) {
            DocumentCategory::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'module' => $cat['module'],
                    'target_roles' => $cat['target_roles'],
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
