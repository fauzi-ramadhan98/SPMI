@extends('layouts.admin')

@section('title', 'Review Detail - ' . $assignment->auditee_label)

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.reports.index', ['cycle_id' => $assignment->audit_cycle_id, 'level' => 'prodi', 'academic_program_id' => $assignment->academic_program_id]) }}" class="btn btn-light border rounded-pill btn-sm px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Rekap
    </a>
    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>Review Detail — {{ $assignment->auditee_label }}</h5>
    <span class="badge bg-dark ms-2">{{ strtoupper($assignment->status) }}</span>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="small text-muted fw-bold text-uppercase" style="font-size:11px; letter-spacing:0.5px;">Auditee</div>
                <div class="fw-bold text-dark">{{ $assignment->auditee_label }}</div>
                <div class="small text-muted">{{ $assignment->auditee_type_label }} • Siklus: {{ $assignment->cycle->name }} ({{ $assignment->cycle->academic_year }})</div>
                <div class="small text-muted mt-1">Auditor: {{ $assignment->auditor_name ?? '—' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3">
                <div class="small text-muted fw-bold text-uppercase" style="font-size:11px;">Ringkasan Temuan & Skor</div>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @php $insts = $assignment->instruments; @endphp
                    <span class="badge bg-danger bg-opacity-10 text-danger border">KTS Mayor: {{ $insts->filter(fn($i)=>str_contains($i->finding_category??'','KTS Mayor'))->count() }}</span>
                    <span class="badge bg-warning bg-opacity-10 text-dark border">KTS Minor: {{ $insts->filter(fn($i)=>str_contains($i->finding_category??'','KTS Minor'))->count() }}</span>
                    <span class="badge bg-success bg-opacity-10 text-success border">Sesuai/Melampaui: {{ $insts->filter(fn($i)=>in_array($i->finding_category,['Melampaui Standar Nasional','Sesuai dengan Standar']))->count() }}</span>
                    <span class="badge bg-light text-dark border">Borang: {{ $insts->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Daftar Temuan Auditor --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-list-check me-2 text-primary"></i>Daftar Temuan Auditor</h6>
        <p class="text-muted small mb-0">Indikator KTS beserta bukti audit lapangan</p>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 18%;">Standar / Indikator</th>
                        <th style="width: 28%;">Catatan Temuan & Bukti</th>
                        <th class="text-center" style="width: 8%;">Nilai</th>
                        <th class="text-center" style="width: 12%;">Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignment->instruments->filter(fn($i)=>str_contains($i->finding_category??'','KTS'))->sortByDesc('score') as $inst)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark small">{{ $inst->criteria }}</div>
                                <div class="text-muted" style="font-size:11px;">{{ Str::limit($inst->indicator, 90) }}</div>
                                @if($inst->audit_question)<div class="text-primary small fst-italic mt-1" style="font-size:11px;"><i class="fa-solid fa-circle-question me-1"></i>{{ Str::limit($inst->audit_question, 100) }}</div>@endif
                            </td>
                            <td>
                                <div class="small text-dark" style="white-space: pre-wrap;">{{ $inst->finding ?? $inst->findings_data[0]['finding'] ?? '—' }}</div>
                                @if(!empty($inst->findings_data))
                                    @foreach($inst->findings_data as $f)
                                        @if(!empty($f['document_link']))<div><a href="{{ $f['document_link'] }}" target="_blank" class="small text-primary"><i class="fa-brands fa-google-drive me-1"></i>Link</a></div>@endif
                                        @if(!empty($f['file_path']))<div><a href="{{ asset('storage/'.$f['file_path']) }}" target="_blank" class="small text-primary"><i class="fa-solid fa-file me-1"></i>File</a></div>@endif
                                    @endforeach
                                @elseif($inst->document_link)
                                    <div><a href="{{ $inst->document_link }}" target="_blank" class="small text-primary"><i class="fa-brands fa-google-drive me-1"></i>{{ $inst->document_link }}</a></div>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge bg-secondary">{{ $inst->score ?? '—' }}</span></td>
                            <td class="text-center"><span class="badge {{ str_contains($inst->finding_category,'Mayor')?'bg-danger':(str_contains($inst->finding_category,'Minor')?'bg-warning text-dark':'bg-secondary') }} rounded-pill">{{ $inst->finding_category ?? '—' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada temuan KTS pada auditee ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Keterkaitan Risk Register --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-triangle-exclamation me-2 text-warning"></i>Keterkaitan dengan Risk Register (Risiko Tinggi)</h6>
        <p class="text-muted small mb-0">Apakah indikator temuan masuk kategori Risiko Tinggi di Risk Register prodi?</p>
    </div>
    <div class="card-body p-0">
        @if($risks->isEmpty())
            <div class="p-4 text-center text-muted small">Tidak ada risiko tinggi terdaftar untuk auditee ini.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 small">
                    <thead class="table-light"><tr><th class="ps-3">Standar</th><th>Indikator</th><th>Risiko</th><th class="text-center">Skor</th><th>Mitigasi</th></tr></thead>
                    <tbody>
                        @foreach($risks as $r)
                            <tr>
                                <td class="ps-3 small">{{ $r->standar_mutu }}</td>
                                <td class="small" style="max-width:220px; white-space: pre-wrap;">{{ Str::limit($r->butir_tilik, 80) }}</td>
                                <td class="small">{{ Str::limit($r->risk_description, 80) }}</td>
                                <td class="text-center"><span class="badge bg-danger">Skor {{ $r->risk_score }}</span></td>
                                <td class="small">{{ Str::limit($r->mitigation_plan, 80) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Form RTL Prodi --}}
<div class="card shadow-sm border-0 rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 px-4">
        <h6 class="fw-bold mb-0"><i class="fa-solid fa-clipboard-list me-2 text-success"></i>Rencana Tindak Lanjut (RTL) Prodi</h6>
        <p class="text-muted small mb-0">Rencana perbaikan dari prodi beserta target tanggal</p>
    </div>
    <div class="card-body p-4">
        @if($evaluation && $evaluation->items->isNotEmpty())
            <div class="small text-muted mb-2">Evaluasi Diri terbaru: {{ $evaluation->name }} ({{ $evaluation->academic_year }}) — {{ $evaluation->items->count() }} indikator</div>
        @endif
        @if($rtl)
            <div class="alert alert-info small">RTM terkait: <strong>{{ $rtl->title ?? 'RTM' }}</strong> ({{ $rtl->status }}) — <a href="{{ route('admin.rtm.show', $rtl->id) }}">Lihat RTM</a></div>
        @else
            <div class="text-muted small">Belum ada RTM untuk siklus ini. Eskalasi akan membuat RTM baru.</div>
        @endif
        <div class="border rounded-3 p-3 bg-light small">
            <div class="fw-bold mb-1">Daftar RTL per temuan (ringkas):</div>
            @forelse($assignment->instruments->filter(fn($i)=>str_contains($i->finding_category??'','KTS'))->take(5) as $inst)
                <div class="mb-1">• <strong>{{ Str::limit($inst->indicator, 60) }}</strong> — Temuan: {{ Str::limit($inst->finding ?? $inst->findings_data[0]['finding'] ?? '—', 80) }}</div>
            @empty
                <div class="text-muted">Belum ada temuan KTS untuk ditindaklanjuti.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Keputusan SPMI --}}
<div class="card shadow-sm border-0 rounded-4 border-top border-4 border-primary">
    <div class="card-body p-4">
        <h6 class="fw-bold mb-3"><i class="fa-solid fa-gavel me-2 text-primary"></i>Keputusan SPMI</h6>
        <form action="{{ route('admin.reports.decide', $assignment->id) }}" method="POST" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label fw-bold small">Catatan / Alasan</label>
                <textarea name="catatan" class="form-control" rows="3" placeholder="Tuliskan alasan keputusan..."></textarea>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button name="decision" value="setujui" class="btn btn-success rounded-pill px-4 fw-bold" onclick="return confirm('Setujui RTL? LHA akan disahkan.')">
                    <i class="fa-solid fa-check me-1"></i> Setujui RTL → LHA Disahkan
                </button>
                <button name="decision" value="tolak" class="btn btn-warning rounded-pill px-4 fw-bold text-dark" onclick="return confirm('Tolak & minta revisi?')">
                    <i class="fa-solid fa-rotate-left me-1"></i> Tolak & Minta Revisi
                </button>
                <button name="decision" value="eskalasi" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="return confirm('Eskalasi ke RTM? Rektor akan memutuskan.')">
                    <i class="fa-solid fa-people-group me-1"></i> Eskalasi ke RTM
                </button>
                <a href="{{ route('admin.reports.index', ['cycle_id' => $assignment->audit_cycle_id]) }}" class="btn btn-light border rounded-pill px-4 ms-auto">Batal</a>
            </div>
        </form>
        <div class="text-muted small mt-3"><i class="fa-solid fa-circle-info me-1"></i>Alur: Auditor Selesai Menilai → SPMI Cek Rekap Tabel (Level 2) → SPMI Validasi Temuan & RTL (Level 3) → LHA Disahkan & Siap Cetak</div>
    </div>
</div>
@endsection
