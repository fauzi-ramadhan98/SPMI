@extends('layouts.public')

@section('title', 'Laporan Kepuasan - ' . config('app.name'))

@section('content')
<div class="py-5 text-white text-center" style="background: linear-gradient(135deg, #1e293b, #1d4ed8);">
    <div class="container py-3">
        <h1 class="fw-bold display-6">Laporan Kepuasan Layanan</h1>
        <p class="text-white-50">STMIK Mardira Indonesia</p>
    </div>
</div>

<div class="container my-5 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 p-md-5">
                    <h4 class="fw-bold mb-3">Rekap Laporan Indeks Kepuasan Layanan</h4>
                    <p class="text-muted" style="line-height:1.8;">
                        Berikut ini adalah rekapitulasi hasil survei kepuasan layanan yang telah dilaksanakan SPMI
                        STMIK Mardira Indonesia. Laporan ini diterbitkan sebagai bentuk transparansi dan akuntabilitas
                        dalam penyelenggaraan mutu layanan akademik dan non-akademik.
                    </p>
                    <hr class="my-4">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border rounded-3 overflow-hidden">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="py-3 px-4">Periode / Semester</th>
                                    <th class="py-3">Jenis Survei</th>
                                    <th class="py-3 text-center">Responden</th>
                                    <th class="py-3 text-center">IKM (Skala 100)</th>
                                    <th class="py-3 text-center">Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 py-3 fw-semibold">Ganjil 2024/2025</td>
                                    <td>Kepuasan Mahasiswa</td>
                                    <td class="text-center"><span class="badge bg-info rounded-pill">-</span></td>
                                    <td class="text-center fw-bold text-success">-</td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">Segera</span></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 py-3 fw-semibold">Genap 2023/2024</td>
                                    <td>Kepuasan Dosen</td>
                                    <td class="text-center"><span class="badge bg-info rounded-pill">-</span></td>
                                    <td class="text-center fw-bold text-success">-</td>
                                    <td class="text-center"><span class="badge bg-light text-muted border">Segera</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-muted small mt-3 fst-italic">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Data laporan akan diperbarui setiap akhir semester setelah proses rekapitulasi survei selesai.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
