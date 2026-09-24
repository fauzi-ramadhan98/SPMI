@extends('layouts.admin')

@section('title', $rtm->title)

@section('content')
@php
    $user = auth()->user();
    $isSpmi = $user->hasRole('spmi');
    $isPimpinan = $user->hasRole('pimpinan');
    $isAuditee = $user->hasRole('prodi|unit');
    $locked = $rtm->status === 'disahkan';
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-landmark me-2 text-primary"></i>{{ $rtm->title }}</h4>
        <p class="text-muted mb-0 small">
            Siklus: <strong>{{ $rtm->cycle?->name }}</strong> ·
            {{ $rtm->meeting_date?->format('d M Y') }} @if($rtm->meeting_time) {{ $rtm->meeting_time->format('H:i') }} @endif
            @if($rtm->location) · {{ $rtm->location }} @endif
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-{{ $rtm->status_color }} bg-opacity-10 text-{{ $rtm->status_color }} border border-{{ $rtm->status_color }} border-opacity-25 rounded-pill px-3 py-2 fw-bold">
            <i class="fa-solid {{ $locked ? 'fa-gavel' : 'fa-hourglass-half' }} me-1"></i>{{ $rtm->status_label }}
        </span>
        <a href="{{ route('admin.rtm.pdf', $rtm->id) }}" class="btn btn-outline-secondary rounded-pill">
            <i class="fa-solid fa-file-pdf me-1"></i> PDF Risalah
        </a>
    </div>
</div>

{{-- Info pengesahan --}}
@if($locked)
<div class="alert alert-success border-0 rounded-4 shadow-sm d-flex align-items-center gap-3">
    <i class="fa-solid fa-gavel fs-4"></i>
    <div>
        <strong>Risalah RTM telah disahkan.</strong> Instruksi tindak lanjut kini resmi berlaku dan telah tersedia di dasbor auditee terkait.
        @if($rtm->approver) Disahkan oleh <strong>{{ $rtm->approver->name }}</strong> @endif
        @if($rtm->approved_at) pada {{ $rtm->approved_at->format('d M Y H:i') }} @endif
    </div>
</div>
@endif

{{-- Executive summary siklus --}}
<div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-chart-simple me-2 text-primary"></i>Ringkasan Hasil Audit — {{ $rtm->cycle?->name }}</h6>
    </div>
    <div class="card-body px-4 py-3">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="p-3 rounded-3 border bg-light"><div class="small text-muted fw-bold">Auditee Diaudit</div><div class="fs-4 fw-bold">{{ $cycleStats['auditees'] }}</div></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 rounded-3 border bg-light"><div class="small text-muted fw-bold">Rata-rata Skor</div><div class="fs-4 fw-bold">{{ $cycleStats['avg_score'] !== null ? number_format($cycleStats['avg_score'], 2) : '—' }}</div></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 rounded-3 border bg-light"><div class="small text-muted fw-bold">KTS Mayor / Minor</div><div class="fs-4 fw-bold text-danger">{{ $cycleStats['kts_mayor'] }} <span class="text-muted fs-6">/</span> <span class="text-warning">{{ $cycleStats['kts_minor'] }}</span></div></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3 rounded-3 border bg-light"><div class="small text-muted fw-bold">Temuan (KTS / OB)</div><div class="fs-4 fw-bold">{{ $cycleStats['kts'] }} <span class="text-muted fs-6">/</span> {{ $cycleStats['ob'] }}</div></div>
            </div>
        </div>
        @if($rtm->agenda)
            <div class="mt-3 small">
                <strong>Agenda:</strong>
                <div class="agenda-text mt-1">
                    @php
                        $agendaNormalized = str_replace(['\\r\\n', '\\n', '\\r'], PHP_EOL, $rtm->agenda);
                        $agendaHtml = '';
                        foreach (preg_split('/\r\n|\r|\n/', $agendaNormalized) as $line) {
                            $line = trim($line);
                            if ($line === '') { continue; }
                            if (preg_match('/^\[(.+)\]$/', $line, $m)) {
                                $agendaHtml .= '<div class="fw-bold text-primary mt-2"><i class="fa-solid fa-building-columns me-1"></i>' . e($m[1]) . '</div>';
                            } elseif (preg_match('/^(KTS|OB) — (.+)$/', $line, $m)) {
                                $badge = $m[1] === 'KTS' ? 'danger' : 'warning';
                                $agendaHtml .= '<div class="mt-2"><span class="badge bg-' . $badge . ' bg-opacity-10 text-' . $badge . ' border border-' . $badge . ' border-opacity-25 rounded-pill me-1">' . $m[1] . '</span><strong>' . e($m[2]) . '</strong></div>';
                            } elseif (preg_match('/^([^:]+):\s?(.*)$/', $line, $m)) {
                                $agendaHtml .= '<div class="ms-3"><span class="text-muted">' . e($m[1]) . ':</span> ' . e($m[2]) . '</div>';
                            } else {
                                $agendaHtml .= '<div>' . e($line) . '</div>';
                            }
                        }
                    @endphp
                    {!! $agendaHtml !!}
                </div>
            </div>
        @endif
    </div>
</div>

@if($isAuditee)
    {{-- ===== AUDITEE: read-only ===== --}}
    <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0"><i class="fa-solid fa-file-lines me-2 text-primary"></i>Risalah Rapat (Notulensi)</h6></div>
        <div class="card-body px-4 py-3">
            {!! nl2br(e($rtm->notulensi)) ?: '<p class="text-muted fst-italic mb-0">Notulensi belum tersedia.</p>' !!}
        </div>
    </div>
    <div class="card card-custom shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0"><i class="fa-solid fa-bullseye me-2 text-primary"></i>Instruksi Tindak Lanjut untuk Auditee Anda</h6></div>
        <div class="card-body px-4 py-3">
            @php
                $myInstructions = $rtm->instructions->filter(fn($i) =>
                    ($i->academic_program_id && $i->academic_program_id === $user->academic_program_id) ||
                    ($i->unit_id && $i->unit_id === $user->unit_id));
            @endphp
            @forelse($myInstructions as $instruction)
                <div class="border rounded-3 p-3 mb-3 bg-white">
                    <div class="d-flex gap-3">
                        <i class="fa-solid fa-arrow-right text-success mt-1"></i>
                        <div>
                            <div>{{ $instruction->instruction }}</div>
                            <div class="small text-muted mt-1">
                                Target: {{ $instruction->target_date?->format('d M Y') ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted fst-italic mb-0">Tidak ada instruksi untuk auditee Anda pada RTM ini.</p>
            @endforelse
        </div>
    </div>
@elseif($isPimpinan)
    {{-- ===== PIMPINAN: baca + approve ===== --}}
    <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0"><i class="fa-solid fa-file-lines me-2 text-primary"></i>Risalah Rapat (Notulensi)</h6></div>
        <div class="card-body px-4 py-3">
            {!! nl2br(e($rtm->notulensi)) ?: '<p class="text-muted fst-italic mb-0">Notulensi belum ditulis oleh SPMI.</p>' !!}
        </div>
    </div>

    <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0"><i class="fa-solid fa-bullseye me-2 text-primary"></i>Instruksi Tindak Lanjut</h6></div>
        <div class="card-body px-4 py-3">
            @forelse($rtm->instructions as $idx => $instruction)
                <div class="border rounded-3 p-3 mb-3 bg-white">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <span class="badge bg-secondary rounded-pill mb-1">{{ $instruction->target_label }}</span>
                            <div class="mt-1">{{ $instruction->instruction }}</div>
                            <div class="small text-muted mt-1">Target selesai: {{ $instruction->target_date?->format('d M Y') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-muted fst-italic mb-0">Belum ada instruksi yang dirumuskan SPMI.</p>
            @endforelse
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="fw-bold mb-1"><i class="fa-solid fa-gavel me-2 text-primary"></i>Ketuk Palu Pengesahan</h6>
                <p class="text-muted small mb-0">Setelah meninjau risalah dan instruksi, sahkan agar instruksi resmi berlaku dan tersedia bagi auditee.</p>
            </div>
            <div class="d-flex gap-2">
                @if($rtm->status === 'notulensi')
                    <form action="{{ route('admin.rtm.approve', $rtm->id) }}" method="POST">
                        @csrf
                        <button type="submit" name="approve" value="1" class="btn btn-success btn-lg rounded-pill px-4 fw-bold shadow">
                            <i class="fa-solid fa-gavel me-2"></i> Sahkan / Approve
                        </button>
                    </form>
                @elseif($locked)
                    <form action="{{ route('admin.rtm.approve', $rtm->id) }}" method="POST">
                        @csrf
                        <button type="submit" name="approve" value="0" class="btn btn-outline-warning rounded-pill px-4">
                            <i class="fa-solid fa-xmark me-1"></i> Batalkan Pengesahan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@else
    {{-- ===== SPMI: workspace lengkap ===== --}}
    <div class="row g-4">
        <div class="col-lg-7">
            {{-- Notulensi --}}
            <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Notulensi / Risalah Rapat</h6>
                </div>
                <div class="card-body px-4 py-3">
                    @if($locked)
                        <div class="p-3 rounded-3 bg-light">{!! nl2br(e($rtm->notulensi)) !!}</div>
                    @else
                    <form action="{{ route('admin.rtm.notulensi', $rtm->id) }}" method="POST">
                        @csrf
                        <textarea name="notulensi" class="form-control" rows="8" placeholder="Ketik risalah rapat di sini — pokok pembahasan, keputusan, dan arahan pimpinan...">{{ $rtm->notulensi }}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                            <div>
                                <span class="fw-bold small text-muted text-uppercase me-2">Status:</span>
                                <select name="status" class="form-select form-select-sm d-inline-block w-auto">
                                    <option value="dijadwalkan" {{ $rtm->status === 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                                    <option value="berlangsung" {{ $rtm->status === 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                                    <option value="notulensi" {{ $rtm->status === 'notulensi' ? 'selected' : '' }}>Notulensi Selesai — Ajukan ke Pimpinan</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Notulensi
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Instruksi --}}
            <div class="card card-custom shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-bullseye me-2 text-primary"></i>Draf Instruksi Tindak Lanjut</h6>
                </div>
                <div class="card-body px-4 py-3">
                    <div class="list-group list-group-flush mb-3">
                        @forelse($rtm->instructions as $instruction)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <span class="badge bg-secondary rounded-pill mb-1">{{ $instruction->target_label }}</span>
                                        <div class="mt-1">{{ $instruction->instruction }}</div>
                                        <div class="small text-muted mt-1">Target selesai: {{ $instruction->target_date?->format('d M Y') ?? '—' }}</div>
                                    </div>
                                    @if(!$locked)
                                        <form action="{{ route('admin.rtm.instructions.destroy', $instruction->id) }}" method="POST" onsubmit="return confirm('Hapus instruksi ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger rounded-circle icon-only-btn" aria-label="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted fst-italic mb-3">Belum ada instruksi. Rumuskan draf tindakan perbaikan per prodi/unit di bawah.</p>
                        @endforelse
                    </div>

                    @if(!$locked)
                    <form action="{{ route('admin.rtm.instructions.store', $rtm->id) }}" method="POST" class="border-top pt-3">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select name="academic_program_id" class="form-select form-select-sm" id="instrProgram">
                                    <option value="">— Program Studi —</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->degree_level }} {{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="unit_id" class="form-select form-select-sm" id="instrUnit">
                                    <option value="">— Unit Kerja —</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <textarea name="instruction" class="form-control" rows="2" placeholder="Isi instruksi / tindakan perbaikan yang harus dilakukan target..." required></textarea>
                            </div>
                            <div class="col-md-6">
                                <input type="date" name="target_date" class="form-control form-control-sm" title="Target selesai">
                            </div>
                            <div class="col-md-6 d-grid">
                                <button class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah Instruksi
                                </button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Peserta --}}
            <div class="card card-custom shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-users me-2 text-primary"></i>Peserta ({{ $rtm->participants->count() }})</h6>
                    @if(!$locked)
                        <a href="{{ route('admin.rtm.edit', $rtm->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fa-solid fa-user-plus me-1"></i> Kelola
                        </a>
                    @endif
                </div>
                <div class="card-body px-4 py-3">
                    @forelse($rtm->participants as $participant)
                        <div class="d-flex align-items-center gap-2 py-1">
                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $participant->roles->pluck('name')->first() }}</span>
                            <span class="small fw-semibold">{{ $participant->name }}</span>
                        </div>
                    @empty
                        <p class="text-muted fst-italic mb-0">Belum ada peserta diundang.</p>
                    @endforelse
                </div>
            </div>

            {{-- Detail jadwal --}}
            <div class="card card-custom shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Informasi RTM</h6>
                </div>
                <div class="card-body px-4 py-3 small">
                    <div class="row mb-1"><div class="col-5 text-muted">Siklus</div><div class="col-7 fw-semibold">{{ $rtm->cycle?->name }}</div></div>
                    <div class="row mb-1"><div class="col-5 text-muted">Tanggal</div><div class="col-7 fw-semibold">{{ $rtm->meeting_date?->format('d M Y') ?? '—' }}</div></div>
                    <div class="row mb-1"><div class="col-5 text-muted">Jam</div><div class="col-7 fw-semibold">{{ $rtm->meeting_time?->format('H:i') ?? '—' }}</div></div>
                    <div class="row mb-1"><div class="col-5 text-muted">Lokasi</div><div class="col-7 fw-semibold">{{ $rtm->location ?? '—' }}</div></div>
                    <div class="row mb-1"><div class="col-5 text-muted">Dibuat oleh</div><div class="col-7 fw-semibold">{{ $rtm->creator?->name }}</div></div>
                    @if($locked)
                        <div class="row mb-1"><div class="col-5 text-muted">Disahkan oleh</div><div class="col-7 fw-semibold">{{ $rtm->approver?->name }}</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const programSelect = document.getElementById('instrProgram');
        const unitSelect = document.getElementById('instrUnit');
        if (programSelect && unitSelect) {
            const clearOther = (kept, cleared) => { cleared.value = ''; };
            programSelect.addEventListener('change', () => clearOther(programSelect, unitSelect));
            unitSelect.addEventListener('change', () => clearOther(unitSelect, programSelect));
        }
    });
</script>
@endpush