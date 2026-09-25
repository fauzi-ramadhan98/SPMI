@extends('layouts.admin')

@section('title', 'Daftar Generate Laporan')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-file-pdf me-2"></i>Daftar Generate Laporan AMI
            <span class="badge bg-info-subtle text-info ms-2">Laporan</span>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary">{{ $reports->count() }} laporan</span>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-chart-simple me-1"></i>Ringkasan Laporan
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success"><i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        {{-- Buat laporan baru --}}
        <form action="{{ route('admin.reports.generated.store') }}" method="POST" class="row g-3 align-items-end p-4 bg-primary bg-opacity-10 rounded-4 mb-4">
            @csrf
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Siklus AMI <span class="text-danger">*</span></label>
                <select name="cycle_id" class="form-select border-0 shadow-sm py-2 px-3 rounded-pill" required>
                    <option value="" disabled>Pilih siklus...</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ request('cycle_id', $cycles->first()?->id) == $cycle->id ? 'selected' : '' }}>
                            {{ $cycle->name }} ({{ $cycle->academic_year }} Sem. {{ $cycle->semester }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Cakupan <span class="text-danger">*</span></label>
                <select name="level" class="form-select border-0 shadow-sm py-2 px-3 rounded-pill" id="levelInput" {{ $isProdi ? 'disabled' : '' }}>
                    <option value="institusi" {{ !$isProdi ? 'selected' : '' }}>Institusi</option>
                    <option value="prodi" {{ $isProdi ? 'selected' : '' }}>Per Prodi</option>
                </select>
                @if($isProdi)<input type="hidden" name="level" value="prodi">@endif
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small text-dark">Program Studi</label>
                <select name="academic_program_id" class="form-select border-0 shadow-sm py-2 px-3 rounded-pill" id="programInput">
                    <option value="">— seluruh auditee —</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->degree_level }} {{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold small text-dark">Jenis Laporan <span class="text-danger">*</span></label>
                <select name="jenis" class="form-select border-0 shadow-sm py-2 px-3 rounded-pill">
                    <option value="klasik">AMI Klasik</option>
                    <option value="berbasis-risiko">AMI Berbasis Risiko</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100 rounded-pill fw-bold shadow">
                    <i class="fa-solid fa-plus me-1"></i> Buat Laporan
                </button>
            </div>
        </form>

        {{-- Daftar laporan --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Siklus</th>
                        <th>Cakupan</th>
                        <th>Jenis</th>
                        <th class="text-center">Lampiran</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $report->cycle?->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $report->cycle?->academic_year }} Sem. {{ $report->cycle?->semester }} • {{ $report->creator?->name ?? '—' }}</div>
                            </td>
                            <td>
                                {{ $report->level === 'prodi' ? 'Per Prodi' : 'Institusi' }}
                                @if($report->level === 'prodi' && $report->program)
                                    <div class="text-muted" style="font-size: 11px;">{{ $report->program->degree_level }} {{ $report->program->name }}</div>
                                @endif
                            </td>
                            <td><span class="badge {{ $report->jenis === 'berbasis-risiko' ? 'bg-warning text-dark' : 'bg-primary' }}">{{ $report->jenis_label }}</span></td>
                            <td class="text-center">{{ $report->attachments_count }}</td>
                            <td>
                                @if($report->status === 'generated' && $report->file_path)
                                    <span class="badge bg-success">Sudah Digenerate</span>
                                @elseif($report->file_path)
                                    <span class="badge bg-warning text-dark">Perlu Generate Ulang</span>
                                @else
                                    <span class="badge bg-secondary">Belum Digenerate</span>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ $report->created_at?->format('d M Y H:i') }}</td>
                            <td class="text-center admin-actions-cell">
                                <div class="admin-table-actions"><div class="admin-table-action-group">
                                    <a href="{{ route('admin.reports.generated.show', $report) }}" class="btn btn-sm btn-outline-primary icon-only-btn admin-table-action" title="Detail &amp; Susun Lampiran" aria-label="Detail"><i class="fa-solid fa-pen-to-square"></i></a>
                                    <a href="{{ route('admin.reports.generated.download', $report) }}" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" title="Download PDF (bila belum digenerate, dibuat ulang dulu)" aria-label="Download"><i class="fa-solid fa-download"></i></a>
                                    <form action="{{ route('admin.reports.generated.destroy', $report) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus laporan beserta file arsipnya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-secondary icon-only-btn admin-table-action" title="Hapus" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div></div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada laporan. Buat laporan pertama lewat form di atas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const level = document.getElementById('levelInput');
    const program = document.getElementById('programInput');
    const sync = () => { if (level && program) program.disabled = level.value === 'institusi'; };
    if (level) level.addEventListener('change', sync);
    sync();
});
</script>
@endpush
