<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QualityStandard;
use App\Models\StandardVersion;

class RevisiStandarController extends Controller
{
    /**
     * P5.1 — Peninjauan & Revisi Standar.
     * Menampilkan daftar standar mutu lengkap dengan versi berjalan,
     * riwayat snapshot versi, dan status revisi (aktif / draft menunggu SK Perubahan).
     * Aksi "Revisi" membuka form edit; setelah disimpan versi naik dan status
     * menjadi draft_revisi sampai SK Perubahan ditandatangani Pimpinan.
     */
    public function index(Request $request)
    {
        $standards = QualityStandard::with(['versions.changer', 'decree'])
            ->when($request->query('status') === 'draft_revisi', fn ($q) => $q->where('revisi_status', 'draft_revisi'))
            ->orderBy('kode_standar')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => QualityStandard::count(),
            'draft' => QualityStandard::where('revisi_status', 'draft_revisi')->count(),
            'versi' => StandardVersion::count(),
        ];

        return view('admin.revisi_standar.index', compact('standards', 'stats'));
    }
}
