<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Kategori Administratif Umum
            ['code' => 'RUK', 'name' => 'Rektorat & Keuangan',        'category' => 'administratif_umum'],
            ['code' => 'AUM', 'name' => 'Administrasi Umum & SDM',    'category' => 'administratif_umum'],
            ['code' => 'HUM', 'name' => 'Humas & Kerja Sama',         'category' => 'administratif_umum'],
            // Kategori Layanan Akademik
            ['code' => 'LAM', 'name' => 'Layanan Akademik & Mahasiswa', 'category' => 'layanan_akademik'],
            ['code' => 'PER', 'name' => 'Perpustakaan',               'category' => 'layanan_akademik'],
            ['code' => 'PMB', 'name' => 'Penerimaan Mahasiswa Baru',  'category' => 'layanan_akademik'],
            // Kategori Penunjang
            ['code' => 'TIK', 'name' => 'Teknologi Informasi & Komunikasi', 'category' => 'penunjang'],
            ['code' => 'SAR', 'name' => 'Sarana & Prasarana',         'category' => 'penunjang'],
            ['code' => 'LAB', 'name' => 'Laboratorium',               'category' => 'penunjang'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                ['name' => $unit['name'], 'category' => $unit['category']]
            );
        }
    }
}
