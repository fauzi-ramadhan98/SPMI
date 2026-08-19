@extends('layouts.admin')

@section('title', 'Kertas Kerja Auditor')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fa-solid fa-clipboard-list me-2 text-primary"></i>Kertas Kerja Auditor</h1>
        <p class="text-muted mb-0">Penugasan audit Anda: unduh Surat Tugas, validasi silang bukti Evaluasi Diri auditee, dan kelola instrumen & temuan.</p>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <select name="cycle_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Siklus</option>
            @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" {{ request('cycle_id') == $cycle->id ? 'selected' : '' }}>
                    {{ $cycle->name }} — {{ $cycle->academic_year }}
                </option>
            @endforeach
        </select>
    </form>
</div>

@forelse ($assignments as $assignment)
<div class="card card-custom shadow-sm mb-3 border-0 border-top border-4 border-primary">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
                <h6 class="fw-bold mb-0">
                    {{ $assignment->auditee_type_label }}: {{ $assignment->auditee_label }}
                    <span class="badge bg-secondary ms-2">{{ $assignment->cycle->name ?? '—' }}</span>
                </h6>
                <div class="text-muted small">
                    Auditor: {{ $assignment->auditor_name ?: ($assignment->auditor->name ?? '—') }}
                    &middot; Periode: {{ $assignment->cycle->academic_year ?? '—' }} {{ $assignment->cycle->semester ?? '' }}
                </div>
            </div>
            <span class="badge bg-light text-dark border">{{ $assignment->status }}</span>
        </div>

        <div class="row g-2">
            <div class="col-md-3">
                <a href="{{ route('admin.surat-tugas.generate', $assignment) }}" class="btn btn-outline-primary w-100 text-start">
                    <i class="fa-solid fa-file-pdf me-2"></i> Unduh Surat Tugas
                </a>
            </div>
            <div class="col-md-3">
                @if ($assignment->evaluation)
                    <a href="{{ route('admin.evaluations.show', $assignment->evaluation) }}" class="btn btn-outline-info w-100 text-start">
                        <i class="fa-solid fa-eye me-2"></i> Bukti ED Auditee
                    </a>
                @else
                    <span class="btn btn-outline-secondary w-100 text-start disabled">
                        <i class="fa-solid fa-eye me-2"></i> ED Belum Tersedia
                    </span>
                @endif
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.audit.instruments.index', $assignment) }}" class="btn btn-outline-success w-100 text-start">
                    <i class="fa-solid fa-list-check me-2"></i> Instrumen / Daftar Tilik
                </a>
            </div>
            <div class="col-md-3">
                <a href="{{ route('admin.audit.findings.index', $assignment) }}" class="btn btn-outline-danger w-100 text-start">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Temuan &amp; PTK
                </a>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card shadow-sm"><div class="card-body text-center text-muted py-5">
    <i class="fa-solid fa-clipboard-list fs-1 mb-3 opacity-25"></i>
    <p class="mb-0">Belum ada penugasan audit untuk Anda.</p>
</div></div>
@endforelse
@endsection