@extends('layouts.admin')

@section('title', 'Dashboard Mutu Institusi')

@section('content')
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Program Studi Aktif</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $programs->count() }}</div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-success text-uppercase mb-1">Siklus AMI Aktif</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $activeCycles }}</div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-info border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-info text-uppercase mb-1">Dokumen Mutu</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $totalDocuments }}</div></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Total Siklus</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $cycles->count() }}</div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-building-columns me-2"></i>Status Evaluasi Diri per Program Studi</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted text-uppercase">
                            <tr>
                                <th class="ps-4">Program Studi</th>
                                <th class="text-center">ED Dibuat</th>
                                <th class="text-center">Sudah Submit</th>
                                <th class="text-center">Terverifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($programs as $program)
                            <tr>
                                <td class="ps-4 py-3">
                                    <span class="fw-bold">{{ $program->degree_level }} {{ $program->name }}</span>
                                    <div class="text-muted small">{{ $program->code }}</div>
                                </td>
                                <td class="text-center">{{ $program->evaluations->count() }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success">{{ $program->evaluations->where('status', 'submitted')->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary">{{ $program->evaluations->where('status', 'verified')->count() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada program studi aktif.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-circle-info me-2"></i>Panduan SPMI</h6></div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2"><i class="fa-solid fa-star me-2 text-warning"></i>Kelola <strong>Standar Mutu</strong> & IKU/IKT (Modul Penetapan P1)</li>
                    <li class="mb-2"><i class="fa-solid fa-list-check me-2 text-primary"></i>Rakit <strong>Daftar Tilik</strong> sebagai instrumen audit</li>
                    <li class="mb-2"><i class="fa-solid fa-rotate me-2 text-success"></i>Atur <strong>Siklus AMI</strong>, Surat Tugas, & alokasi auditor</li>
                    <li class="mb-2"><i class="fa-solid fa-chart-pie me-2 text-info"></i>Unduh <strong>Laporan LHA</strong> dari menu Laporan AMI</li>
                    <li class="mb-0"><i class="fa-solid fa-clipboard-check me-2 text-danger"></i>Pantau <strong>RTL</strong> temuan KTS prodi</li>
                </ul>
                <a href="{{ route('admin.audit.cycles.create') }}" class="btn btn-primary btn-custom w-100 mt-4">
                    <i class="fa-solid fa-plus me-2"></i>Buat Siklus AMI Baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection