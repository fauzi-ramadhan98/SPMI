@extends('layouts.admin')

@section('title', 'Rapat Tindak Lanjut (RTL)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-clipboard-check me-2 text-success"></i>Rapat Tindak Lanjut (RTL)</h4>
        <p class="text-muted mb-0 small">Modul Pengendalian (P3) — Monitoring progres perbaikan temuan lintas siklus dan instruksi pimpinan</p>
    </div>
</div>

{{-- Filter --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Siklus AMI</label>
                <select name="cycle_id" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                    <option value="">Semua Siklus</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ request('cycle_id') == $cycle->id ? 'selected' : '' }}>{{ $cycle->name }} ({{ $cycle->academic_year }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Jenis Temuan</label>
                <select name="type" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="KTS" {{ request('type') == 'KTS' ? 'selected' : '' }}>KTS</option>
                    <option value="OB" {{ request('type') == 'OB' ? 'selected' : '' }}>OB</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Status Tindak Lanjut</label>
                <select name="status" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm shadow-sm" placeholder="Standar / uraian temuan...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                <a href="{{ route('admin.pimpinan.rtl') }}" class="btn btn-sm btn-light border rounded-pill px-3 shadow-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Daftar Temuan --}}
<div class="d-flex flex-column gap-3">
    @forelse($findings as $f)
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header {{ $f->type == 'KTS' ? 'bg-danger text-white' : 'bg-warning' }} py-2 px-4 d-flex justify-content-between align-items-center">
            <span class="fw-bold fs-6">
                <i class="fa-solid {{ $f->type == 'KTS' ? 'fa-xmark' : 'fa-eye' }} me-1"></i>
                {{ $f->type }} — {{ $f->assignment?->auditee_label ?? '—' }}
            </span>
            <span class="badge shadow-sm px-3 py-1 text-uppercase
                @if($f->status == 'verified') bg-success text-white
                @elseif($f->status == 'closed') bg-secondary text-white
                @elseif($f->status == 'in_progress') bg-warning text-dark
                @else bg-white text-dark
                @endif">
                @if($f->status == 'verified') <i class="fa-solid fa-circle-check me-1"></i>Verified
                @elseif($f->status == 'closed') <i class="fa-solid fa-lock me-1"></i>Closed
                @elseif($f->status == 'in_progress') <i class="fa-solid fa-rotate me-1"></i>In Progress
                @else <i class="fa-solid fa-circle-dot me-1"></i>Open
                @endif
            </span>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Standar / Kriteria</div>
                    <div class="fw-semibold text-dark">{{ $f->criteria }}</div>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Siklus AMI</div>
                    <div class="fw-semibold text-dark">{{ $f->assignment?->cycle?->name ?? '—' }} <span class="text-muted">({{ $f->assignment?->cycle?->academic_year ?? '' }})</span></div>
                </div>
            </div>
            <div class="mb-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Uraian Temuan</div>
                <p class="text-muted small mb-0" style="white-space: pre-wrap;">{{ $f->description }}</p>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Rencana Perbaikan (Auditee)</div>
                    <p class="text-muted small mb-0" style="white-space: pre-wrap;">{{ $f->corrective_action ?: '—' }}</p>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Akar Masalah</div>
                    <p class="text-muted small mb-0" style="white-space: pre-wrap;">{{ $f->root_cause ?: '—' }}</p>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Target Penyelesaian</div>
                    <p class="fw-semibold mb-0">{{ $f->target_date ? $f->target_date->format('d M Y') : '—' }}</p>
                </div>
            </div>

            <hr class="my-3">
            @hasrole('pimpinan')
            <form action="{{ route('admin.pimpinan.rtl.note', $f->id) }}" method="POST">
                @csrf
                <label class="form-label fw-bold small text-muted text-uppercase"><i class="fa-solid fa-pen me-1"></i>Instruksi / Catatan Pimpinan</label>
                <textarea name="pimpinan_note" class="form-control border-secondary shadow-sm" rows="2" placeholder="Tuliskan arahan pimpinan untuk tindak lanjut temuan ini...">{{ $f->pimpinan_note }}</textarea>
                <div class="text-end mt-2">
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Simpan Instruksi
                    </button>
                </div>
            </form>
            @else
            <div class="text-muted small">
                <i class="fa-solid fa-lock me-1"></i> Instruksi RTL dikelola pimpinan (SPMI bersifat read-only).
                @if($f->pimpinan_note)<div class="mt-2 fw-semibold text-dark bg-light border rounded-2 p-3 text-break" style="white-space: pre-wrap;">{{ $f->pimpinan_note }}</div>@endif
            </div>
            @endhasrole
        </div>
    </div>
    @empty
    <div class="card shadow-sm border-0 rounded-4 p-5 text-center">
        <i class="fa-solid fa-shield-halved fs-1 mb-3 text-success opacity-50"></i>
        <h6 class="fw-bold text-dark">Tidak Ada Temuan</h6>
        <p class="text-muted small mb-0">Belum ada temuan yang cocok dengan filter yang dipilih.</p>
    </div>
    @endforelse
</div>

@if($findings->hasPages())
<div class="mt-4">
    {{ $findings->withQueryString()->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection
