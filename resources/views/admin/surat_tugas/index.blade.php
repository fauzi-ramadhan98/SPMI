@extends('layouts.admin')

@section('title', 'Surat Tugas & Jadwal Visitasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">Surat Tugas & Jadwal Visitasi</h1>
        <p class="text-muted mb-0">Generate dokumen Surat Tugas Auditor dan jadwal visitasi AMI per siklus (PDF).</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.surat-tugas.index') }}" class="row g-2 mb-3 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-1">Filter Siklus</label>
        <select name="cycle_id" class="form-select">
            <option value="">— Semua Siklus —</option>
            @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}" {{ $cycleId == $cycle->id ? 'selected' : '' }}>
                    {{ $cycle->name }} — {{ $cycle->academic_year }} ({{ $cycle->status }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-primary">Filter</button>
    </div>
    <div class="col-md-5 text-md-end">
        @foreach ($cycles as $cycle)
            @if ($cycle->assignments()->count() > 0)
                <a href="{{ route('admin.surat-tugas.jadwal', $cycle) }}" class="btn btn-outline-success">
                    <i class="fa-solid fa-calendar-days"></i> Jadwal Visitasi {{ $cycle->name }}
                </a>
            @endif
        @endforeach
    </div>
</form>

<div class="card">
    <div class="card-body table-responsive">
        @forelse ($assignments as $assignment)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between flex-wrap gap-2">
                    <div>
                        <strong>{{ $assignment->auditee_type_label }}:</strong> {{ $assignment->auditee_label }}
                        <span class="badge bg-secondary">{{ $assignment->cycle->name ?? '—' }}</span>
                        <span class="badge bg-info text-dark">{{ $assignment->cycle->status ?? '—' }}</span>
                    </div>
                    <div>
                        <a href="{{ route('admin.surat-tugas.generate', $assignment) }}" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-file-pdf"></i> Surat Tugas PDF
                        </a>
                    </div>
                </div>
                <div class="small text-muted mt-2">
                    Auditor: {{ $assignment->auditor_name ?: ($assignment->auditor->name ?? '—') }} ({{ $assignment->auditor_type }}{{ $assignment->auditor_role ? ' - ' . $assignment->auditor_role : '' }})
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada penugasan auditor. Buat alokasi auditor terlebih dahulu pada menu Alokasi Auditor.</p>
        @endforelse
    </div>
</div>
@endsection