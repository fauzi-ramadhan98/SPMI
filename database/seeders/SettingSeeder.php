<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'institution_name'       => ['STMIK Mardira Indonesia', 'general', 'Nama Institusi'],
            'institution_short_name' => ['STMIK Mardira', 'general', 'Nama Singkat'],
            'academic_year'          => ['2025/2026', 'general', 'Tahun Akademik Aktif'],
            'semester'               => ['Ganjil', 'general', 'Semester Aktif'],
            'address'                => ['', 'general', 'Alamat'],
            'phone'                  => ['', 'general', 'Telepon'],
            'email'                  => ['', 'general', 'Email'],
            'logo'                   => ['images/logo.png', 'general', 'Logo'],
            'report_kode'            => ['STMIKMI.LPMI.AMI.VIII.1', 'laporan', 'Kode Dokumen Laporan AMI'],
            'report_edisi'           => ['2', 'laporan', 'Edisi Dokumen Laporan AMI'],
            'report_cover_enabled'   => ['1', 'laporan', 'Tampilkan Cover Laporan'],
            'report_cover_image'     => ['', 'laporan', 'Cover Kustom Laporan'],
        ];

        foreach ($defaults as $key => [$value, $group, $label]) {
            Setting::firstOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'label' => $label]
            );
        }
    }
}