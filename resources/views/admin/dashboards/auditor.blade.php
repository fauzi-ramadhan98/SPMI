@extends('layouts.admin')

@section('title', 'Dashboard Auditor')

@section('content')
<div class="row g-4 mb-5">
    <div class="col-xl-4 col-md-6">
        <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Penugasan Saya</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $assignments->count() }}</div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Temuan Terbuka</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $pendingFindings }}</div></div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-success text-uppercase mb-1">Selesai</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $assignments->where('status', 'selesai')->count() }}</div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-clipboard-list me-2"></i>Daftar Tugas Audit</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted text-uppercase">
                            <tr><th class="ps-4">Auditee</th><th>Siklus</th><th>Status</th><th class="text-center pe-4">Aksi</th></tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $a)
                            <tr>
                                <td class="ps-4 py-3">{{ $a->auditee_label }}</td>
                                <td>{{ $a->cycle?->name ?? '-' }}</td>
                                <td>
                                    @if($a->status === 'selesai')<span class="badge bg-success-subtle text-success">Selesai</span>
                                    @elseif($a->status === 'berlangsung')<span class="badge bg-warning-subtle text-warning">Berlangsung</span>
                                    @else<span class="badge bg-secondary-subtle text-secondary">Menunggu</span>@endif
                                </td>
                                <td class="text-center pe-4 admin-actions-cell"><div class="admin-table-actions">
                                    <a href="{{ route('admin.audit.instruments.index', $a->id) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Kertas Kerja" aria-label="Kertas Kerja"><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i></a>
                                    <a href="{{ route('admin.audit.findings.index', $a->id) }}" class="btn btn-sm btn-outline-info icon-only-btn admin-table-action" title="Temuan" aria-label="Temuan"><i aria-hidden="true" class="fa-solid fa-magnifying-glass"></i></a>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada penugasan untuk Anda.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h6></div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.kertas-kerja.index') }}" class="btn btn-outline-primary text-start border-2">
                        <i class="fa-solid fa-clipboard-list me-2 fa-fw"></i> Kertas Kerja Auditor
                    </a>
                    <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-outline-info text-start border-2">
                        <i class="fa-solid fa-clipboard-user me-2 fa-fw"></i> Lihat Penugasan
                    </a>
                    <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-secondary text-start border-2">
                        <i class="fa-solid fa-eye me-2 fa-fw"></i> Lihat Evaluasi Diri
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection