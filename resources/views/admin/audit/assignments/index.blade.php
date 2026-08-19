@extends('layouts.admin')

@section('content')
@php
    $auditee = auth()->user()->hasAnyRole(['prodi', 'unit']);
    $isAuditor = auth()->user()->hasRole('auditor');
@endphp
@section('title', $auditee ? 'Informasi Pelaksanaan Audit - AMI' : ($isAuditor ? 'Jadwal Penugasan Saya - AMI' : 'Alokasi & Penugasan Auditor - AMI'))
<div class="card card-custom shadow-sm mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-0">
        <div>
            <h5 class="mb-0 fw-bold">{{ $auditee
                ? 'Informasi Pelaksanaan Audit'
                : ($isAuditor ? 'Jadwal Penugasan Saya' : 'Alokasi & Penugasan Auditor') }}</h5>
            <p class="text-muted small mb-0 mt-1">{{ $auditee
                ? 'Jadwal dan auditor yang ditugaskan untuk mengaudit program studi / unit kerja Anda.'
                : ($isAuditor
                    ? 'Daftar program studi / unit kerja yang Anda audit serta Surat Tugas resmi dari SPMI.'
                    : 'Pemetaan auditor ke setiap program studi / unit kerja yang diaudit.') }}</p>
        </div>
        @hasrole('spmi')
        <div class="d-flex gap-2">
            @if(request('cycle_id'))
                <a href="{{ route('admin.surat-tugas.jadwal', request('cycle_id')) }}" class="btn btn-success fw-semibold rounded-pill px-4 shadow-sm" target="_blank" title="Cetak jadwal visitasi seluruh auditee pada siklus terpilih">
                    <i class="fa-solid fa-print me-1"></i> Cetak Surat Tugas
                </a>
            @else
                <a href="{{ route('admin.surat-tugas.index') }}" class="btn btn-success fw-semibold rounded-pill px-4 shadow-sm" title="Kelola pencetakan Surat Tugas & Jadwal Visitasi">
                    <i class="fa-solid fa-print me-1"></i> Cetak Surat Tugas
                </a>
            @endif
            <a href="{{ route('admin.audit.assignments.create') }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Tambah Alokasi
            </a>
        </div>
        @endhasrole
    </div>
    
    <div class="card-body p-4 bg-light bg-opacity-50 border-top mt-3">
        <form action="{{ route('admin.audit.assignments.index') }}" method="GET" class="row gx-3 align-items-center">
            <div class="col-md-5">
                <label class="form-label text-muted small fw-bold mb-1">Filter berdasarkan Siklus <span class="text-danger">*</span></label>
                <div class="d-flex">
                    <select name="cycle_id" class="form-select border-0 shadow-sm rounded-start-pill py-2">
                        <option value="">-- Tampilkan Semua Siklus --</option>
                        @foreach($cycles as $c)
                            <option value="{{ $c->id }}" {{ request('cycle_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->academic_year }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary shadow-sm fw-bold rounded-end-pill px-4 border-0">Filter</button>
                    @if(request('cycle_id'))
                        <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-light shadow-sm ms-2 rounded-pill px-3 border-0 text-danger"><i class="fa-solid fa-xmark"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="card-body p-0 border-top">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Auditee (Teraudit)</th>
                        <th class="py-3">Auditor Ditugaskan</th>
                        <th class="py-3">Siklus AMI</th>
                        <th class="py-3 text-center">Status Audit</th>
                        <th class="py-3 text-center pe-4">Instrumen & Temuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                    <tr class="border-bottom">
                        <td class="ps-4 py-4">
                            <h6 class="fw-bold mb-1 text-dark">{{ $assignment->auditee_label }}</h6>
                            @if($assignment->academicProgram)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1"><i class="fa-solid fa-building-columns me-1"></i> {{ $assignment->academicProgram->faculty }}</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border px-2 py-1"><i class="fa-solid fa-building me-1"></i> {{ $assignment->unit->category_label ?? 'Unit' }}</span>
                            @endif
                            <span class="badge bg-light text-dark border px-2 py-1 ms-1"><i class="fa-solid fa-tag me-1"></i>{{ $assignment->auditee_type_label }}</span>
                        </td>
                        <td class="py-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-25 text-info rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold flex-shrink-0" style="width: 35px; height: 35px;">
                                    <i class="fa-solid fa-user-tie"></i>
                                </div>
                                <div>
                                    <span class="fw-semibold text-dark d-block">{{ $assignment->auditor_name }}</span>
                                    <span class="text-muted small"><i class="fa-solid fa-id-card me-1"></i> {{ $assignment->auditor_nidn ?? '-' }} ({{ $assignment->auditor_type }}{{ $assignment->auditor_role ? ' - ' . $assignment->auditor_role : '' }})</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                            <span class="text-muted small"><i class="fa-solid fa-rotate fa-fw me-1"></i> {{ $assignment->cycle->name }}</span>
                        </td>
                        <td class="py-4 text-center">
                            {{-- Status audit berjalan otomatis (system-driven), bukan dropdown manual --}}
                            @if($assignment->status == 'selesai')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-check fa-fw me-1"></i> Selesai</span>
                            @elseif($assignment->status == 'finalisasi')
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-lock fa-fw me-1"></i> Finalisasi</span>
                            @elseif($assignment->status == 'berlangsung')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-hourglass-half fa-fw me-1"></i> Berlangsung</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1"><i class="fa-regular fa-clock fa-fw me-1"></i> Pending</span>
                            @endif
                            @hasrole('spmi')
                            <div class="form-text text-muted small mt-1" style="font-size: 0.7rem;">Otomatis: Pending → Berlangsung → Finalisasi → Selesai</div>
                            @endhasrole
                        </td>
                        <td class="py-4 text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                @hasrole('auditor')
                                <a href="{{ route('admin.surat-tugas.generate', $assignment->id) }}" class="btn btn-outline-success btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Unduh Surat Tugas resmi dari SPMI">
                                    <i class="fa-solid fa-file-pdf me-1"></i> Surat Tugas
                                </a>
                                @if(in_array($assignment->status, ['pending', 'berlangsung']))
                                <form action="{{ route('admin.audit.assignments.update-status', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Finalisasi Kertas Kerja ini? Setelah difinalisasi, borang dan temuan terkunci.');">
                                    @csrf
                                    <input type="hidden" name="action" value="finalisasi">
                                    <button type="submit" class="btn btn-outline-info btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Kunci Kertas Kerja agar borang & temuan tidak dapat diubah lagi">
                                        <i class="fa-solid fa-lock me-1"></i> Finalisasi
                                    </button>
                                </form>
                                @endif
                                @endhasrole
                                @hasrole('spmi')
                                <a href="{{ route('admin.surat-tugas.generate', $assignment->id) }}" class="btn btn-outline-success btn-sm rounded px-3 py-1 fw-bold shadow-sm" target="_blank" title="Unduh Surat Tugas auditor ini (PDF)">
                                    <i class="fa-solid fa-file-pdf me-1"></i> Surat Tugas
                                </a>
                                @if(in_array($assignment->status, ['finalisasi', 'selesai']))
                                    <form action="{{ route('admin.audit.assignments.update-status', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Buka kembali kertas kerja menjadi Berlangsung? Auditor dapat memperbaiki borang/temuan.');">
                                        @csrf
                                        <input type="hidden" name="action" value="buka_kembali">
                                        <button type="submit" class="btn btn-outline-warning btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Kembalikan ke status Berlangsung agar auditor dapat edit kembali">
                                            <i class="fa-solid fa-rotate-left me-1"></i> Buka Kembali
                                        </button>
                                    </form>
                                    @if($assignment->status === 'finalisasi')
                                    <form action="{{ route('admin.audit.assignments.update-status', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tetapkan hasil audit ini menjadi SELESAI? Laporan AMI dapat langsung diterbitkan.');">
                                        @csrf
                                        <input type="hidden" name="action" value="selesai">
                                        <button type="submit" class="btn btn-outline-success btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Dari Finalisasi: tetapkan hasil audit sebagai Selesai">
                                            <i class="fa-solid fa-check me-1"></i> Selesaikan
                                        </button>
                                    </form>
                                    @endif
                                @endif
                                @endhasrole
                                @if($auditee)
                                    {{-- Prodi/Unit: Instrumen mengarah ke Evaluasi Diri milik auditee --}}
                                    <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-primary btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Lihat Evaluasi Diri Anda untuk dibandingkan dengan standar yang akan diaudit">
                                        <i class="fa-solid fa-clipboard-list me-1"></i> Instrumen
                                    </a>
                                    {{-- Prodi/Unit: Temuan hanya boleh dibaca setelah audit dikunci (finalisasi/selesai) --}}
                                    @if(in_array($assignment->status, ['finalisasi', 'selesai']))
                                        <a href="{{ route('admin.audit.findings.index', $assignment->id) }}" class="btn btn-outline-danger btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Hasil Temuan Audit telah final">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Temuan
                                        </a>
                                    @else
                                        <span class="btn btn-outline-secondary btn-sm rounded px-3 py-1 fw-bold shadow-sm disabled" style="pointer-events:none;"
                                            title="Temuan tersedia setelah status audit dikunci (Finalisasi/Selesai)">
                                            <i class="fa-solid fa-lock me-1"></i> Temuan
                                        </span>
                                    @endif
                                @else
                                <a href="{{ route('admin.audit.instruments.index', $assignment->id) }}" class="btn btn-outline-primary btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Pengisian Borang / Instrumen">
                                    <i class="fa-solid fa-clipboard-list me-1"></i> Instrumen
                                </a>
                                <a href="{{ route('admin.audit.findings.index', $assignment->id) }}" class="btn btn-outline-danger btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Catatan Temuan (PTK)">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Temuan
                                </a>
                                @endif

                                @hasrole('spmi')
                                @if($assignment->status === 'pending')
                                <form action="{{ route('admin.audit.assignments.destroy', $assignment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus alokasi ini beserta seluruh nilai dan temuannya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded shadow-sm" title="Hapus Alokasi">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <span class="btn btn-outline-secondary btn-sm rounded shadow-sm disabled" style="pointer-events:none;" title="Hapus dikunci: alokasi sudah {{ $assignment->status }}">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                @endif
                                @endhasrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-clipboard-user fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">{{ $auditee
                                    ? 'Belum ada jadwal audit untuk program studi / unit kerja Anda.'
                                    : ($isAuditor
                                        ? 'Belum ada penugasan audit untuk Anda. Jadwal akan muncul setelah SPMI menetapkan alokasi.'
                                        : 'Belum ada alokasi auditor yang ditambahkan.') }}</p>
                                @hasrole('spmi')
                                <a href="{{ route('admin.audit.assignments.create') }}" class="btn btn-sm btn-outline-primary mt-3 rounded-pill px-4">Buat Sekarang</a>
                                @endhasrole
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($assignments->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $assignments->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
