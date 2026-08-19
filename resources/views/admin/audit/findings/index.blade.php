@extends('layouts.admin')

@section('title', 'Laporan Temuan: ' . $assignment->auditee_label)

@section('content')
<div class="row align-items-stretch">
    <!-- List Findings -->
    <div class="col-lg-7 d-flex flex-column">
        <div class="card card-custom shadow-sm flex-fill border-0 border-top border-4 border-danger">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center mb-1">
                    <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <h5 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Permintaan Tindakan Koreksi (PTK)</h5>
                </div>
            </div>
            
            <div class="card-body p-4 pt-3">
                <p class="text-muted small mb-4">Mencatat Ketidaksesuaian (KTS) dan Observasi (OB) hasil temuan audit untuk dipantau proses perbaikannya.</p>

                <div class="d-flex flex-column gap-3">
                    @forelse($findings as $idx => $f)
                    <div class="card shadow-sm border {{ $f->type == 'KTS' ? 'border-danger' : 'border-warning' }} rounded-3 overflow-hidden">
                        <div class="card-header {{ $f->type == 'KTS' ? 'bg-danger text-white' : 'bg-warning' }} py-2 px-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-6"><i class="fa-solid {{ $f->type == 'KTS' ? 'fa-xmark' : 'fa-eye' }} me-1"></i> Temuan #{{ $idx + 1 }} - {{ $f->type }}</span>
                            <span class="badge shadow-sm px-3 py-1
                                @if($f->status == 'verified') bg-success text-white
                                @elseif($f->status == 'closed') bg-secondary text-white
                                @elseif($f->status == 'in_progress') bg-warning text-dark
                                @else bg-white {{ $f->type == 'KTS' ? 'text-danger' : 'text-dark' }}
                                @endif
                                text-uppercase">
                                @if($f->status == 'verified') <i class="fa-solid fa-circle-check me-1"></i>Diverifikasi
                                @elseif($f->status == 'closed') <i class="fa-solid fa-lock me-1"></i>Selesai
                                @elseif($f->status == 'in_progress') <i class="fa-solid fa-rotate me-1"></i>Sedang Diperbaiki
                                @else <i class="fa-solid fa-circle-dot me-1"></i>Open
                                @endif
                            </span>
                        </div>
                        <div class="card-body p-4 bg-light">
                            <h6 class="fw-bold text-dark mb-1">{{ $f->criteria }}</h6>
                            <p class="text-muted small mb-3 pb-3 border-bottom border-secondary border-opacity-25">{{ $f->description }}</p>

                            <form action="{{ route('admin.audit.findings.update', $f->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Sanggahan / Klarifikasi Prodi</label>
                                    <textarea name="prodi_clarification" class="form-control" rows="2" placeholder="Isi jika ada sanggahan atau klarifikasi terhadap temuan ini..." {{ auth()->user()->hasAnyRole(['prodi', 'unit']) || auth()->user()->hasAnyRole(['spmi', 'auditor']) ? '' : 'readonly' }}>{{ $f->prodi_clarification }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Akar Masalah (Analisis Auditee)</label>
                                    <textarea name="root_cause" class="form-control" rows="2" placeholder="Mengapa bisa terjadi KTS/OB..." {{ auth()->user()->hasAnyRole(['prodi', 'unit']) || auth()->user()->hasAnyRole(['spmi', 'auditor']) ? '' : 'readonly' }}>{{ $f->root_cause }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Rencana Tindakan Koreksi / Perbaikan</label>
                                    <textarea name="corrective_action" class="form-control" rows="2" placeholder="Apa yang akan dilakukan untuk memperbaiki..." {{ auth()->user()->hasAnyRole(['prodi', 'unit']) || auth()->user()->hasAnyRole(['spmi', 'auditor']) ? '' : 'readonly' }}>{{ $f->corrective_action }}</textarea>
                                </div>
                                @if(auth()->user()->hasAnyRole(['prodi', 'unit']))
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label text-muted small fw-bold">Keputusan Temuan</label>
                                        @if($f->prodi_decision)
                                            <span class="d-block py-2 badge {{ $f->prodi_decision === 'setuju' ? 'bg-success' : 'bg-danger' }}">{{ $f->prodi_decision === 'setuju' ? '✓ Disetujui' : '✗ Ditolak' }}</span>
                                        @endif
                                        <select name="prodi_decision" class="form-select">
                                            <option value="">-- Pilih --</option>
                                            <option value="setuju" @selected($f->prodi_decision === 'setuju')>Setuju</option>
                                            <option value="tolak" @selected($f->prodi_decision === 'tolak')>Tolak</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label text-muted small fw-bold">Alasan Keputusan</label>
                                        <textarea name="prodi_decision_note" class="form-control" rows="2" placeholder="Wajib diisi jika menolak (klarifikasi alasan)">{{ $f->prodi_decision_note }}</textarea>
                                    </div>
                                </div>
                                @endif
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-bold">Target Penyelesaian</label>
                                        <input type="date" name="target_date" class="form-control" value="{{ $f->target_date ? \Carbon\Carbon::parse($f->target_date)->format('Y-m-d') : '' }}" {{ auth()->user()->hasAnyRole(['prodi', 'unit']) || auth()->user()->hasAnyRole(['spmi', 'auditor']) ? '' : 'readonly' }}>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-bold">Status Verifikasi Tindak Lanjut</label>
                                        @if(auth()->user()->hasAnyRole(['spmi', 'auditor']))
                                            <select name="status" class="form-select">
                                                <option value="open" {{ $f->status == 'open' ? 'selected' : '' }}>Open (Belum Ditindaklanjuti)</option>
                                                <option value="in_progress" {{ $f->status == 'in_progress' ? 'selected' : '' }}>In Progress (Sedang Diperbaiki)</option>
                                                <option value="closed" {{ $f->status == 'closed' ? 'selected' : '' }}>Closed (Tindakan Selesai)</option>
                                                <option value="verified" {{ $f->status == 'verified' ? 'selected' : '' }}>✅ Verified (Selesai &amp; Diverifikasi Auditor)</option>
                                            </select>
                                        @else
                                            <input type="text" class="form-control bg-light" value="{{ match($f->status) { 'open' => 'Open', 'in_progress' => 'Sedang Diperbaiki', 'closed' => 'Tindakan Selesai', 'verified' => 'Diverifikasi', default => $f->status } }}" readonly>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary fw-semibold rounded shadow-sm px-4">Simpan Progress</button>
                                </div>
                            </form>

                            {{-- Bukti perbaikan (RTL) per temuan --}}
                            <div class="border rounded-3 p-3 mt-3 bg-white">
                                <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-paperclip me-1 text-muted"></i> Bukti Perbaikan RTL</h6>
                                @forelse ($f->attachments as $att)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2 small">
                                        <div>
                                            @if($att->file_path)
                                                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank">
                                                    <i class="fa-solid fa-file me-1 text-primary"></i>{{ $att->file_name }}
                                                </a>
                                            @elseif($att->link)
                                                <a href="{{ $att->link }}" target="_blank"><i class="fa-solid fa-link me-1 text-primary"></i>{{ $att->link }}</a>
                                            @else
                                                <span class="text-muted">Lampiran</span>
                                            @endif
                                            @if($att->title)<span class="text-muted ms-2">— {{ $att->title }}</span>@endif
                                        </div>
                                        @hasrole('spmi|auditor|prodi|unit')
                                        <form action="{{ route('admin.audit.findings.attachments.destroy', [$f, $att]) }}" method="POST" onsubmit="return confirm('Hapus bukti?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm text-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                        @endhasrole
                                    </div>
                                @empty
                                    <p class="text-muted small mb-2">Belum ada bukti perbaikan.</p>
                                @endforelse
                                <form action="{{ route('admin.audit.findings.attachments.store', $f) }}" method="POST" enctype="multipart/form-data" class="row g-2 mt-1">
                                    @csrf
                                    <div class="col-md-4"><input class="form-control form-control-sm" name="category" placeholder="Kategori"></div>
                                    <div class="col-md-5"><input class="form-control form-control-sm" type="file" name="file"></div>
                                    <div class="col-md-3"><input class="form-control form-control-sm" name="link" placeholder="atau URL"></div>
                                    <div class="col-12 text-end">
                                        <button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-plus"></i> Tambah Bukti</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5 bg-light rounded-3 border border-light">
                        <i class="fa-solid fa-shield-halved text-success fs-1 mb-3 opacity-50"></i>
                        <h6 class="fw-bold text-dark">Luar Biasa, Tidak Ada Temuan</h6>
                        <p class="text-muted small mb-0">Belum ada catatan ketidaksesuaian yang ditambahkan oleh tim auditor.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Create Finding -->
    @if(!in_array($assignment->status, ['finalisasi', 'selesai']))
    @hasrole('spmi|auditor')
    <div class="col-lg-5 d-flex flex-column">
        <div class="card card-custom shadow-sm bg-light flex-fill border-0">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-plus-circle me-2 text-primary"></i> Input Temuan Baru</h6>
            </div>
            <div class="card-body p-4 pt-3">
                <form action="{{ route('admin.audit.findings.store', $assignment->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Tipe Temuan <span class="text-danger">*</span></label>
                        <select name="type" class="form-select shadow-sm border-0" required>
                            <option value="KTS">Ketidaksesuaian (KTS)</option>
                            <option value="OB">Observasi (OB)</option>
                        </select>
                    </div>

                    {{-- Standar Kriteria — dari instrumen atau manual --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">
                            Standar / Kriteria Referensi <span class="text-danger">*</span>
                        </label>

                        @if($instruments->count() > 0)
                        {{-- Dropdown dari instrumen yang ada --}}
                        <div class="mb-2">
                            <select id="sel-instrument" class="form-select form-select-sm shadow-sm border-0 bg-light">
                                <option value="">— Pilih dari instrumen audit (opsional) —</option>
                                @foreach($instruments as $inst)
                                <option
                                    value="{{ $inst->id }}"
                                    data-criteria="{{ $inst->criteria }}"
                                    data-indicator="{{ $inst->indicator }}"
                                    data-finding="{{ $inst->finding }}"
                                    data-category="{{ $inst->finding_category }}"
                                    data-score="{{ $inst->score }}">
                                    [{{ $inst->finding_category ?? '—' }}]
                                    {{ Str::limit($inst->criteria, 55) }}
                                    @if($inst->score !== null)
                                        · Skor {{ $inst->score }}/4
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            <div class="form-text mt-1">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                                Pilih instrumen untuk mengisi otomatis, atau ketik manual di bawah.
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info border-0 py-2 px-3 small mb-2 rounded-3">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Belum ada instrumen pada assignment ini. Isi instrumen terlebih dahulu atau ketik manual.
                        </div>
                        @endif

                        <input type="text" name="criteria" id="input-criteria"
                            class="form-control shadow-sm border-0" required
                            placeholder="Contoh: Std 5 - Sarana Prasarana">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Deskripsi Uraian Temuan <span class="text-danger">*</span></label>
                        <textarea name="description" id="input-description"
                            class="form-control shadow-sm border-0" required rows="4"
                            placeholder="Uraikan fakta temuan di lapangan..."></textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-danger fw-bold rounded-pill py-2 shadow">
                            <i class="fa-solid fa-plus me-1"></i> Catat Temuan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endhasrole
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('sel-instrument');
    if (!sel) return;

    sel.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;

        // Isi field Standar / Kriteria
        const criteriaInput = document.getElementById('input-criteria');
        criteriaInput.value = opt.dataset.criteria || '';

        // Susun deskripsi temuan dari data instrumen
        const descInput = document.getElementById('input-description');
        let desc = '';

        if (opt.dataset.indicator && opt.dataset.indicator.trim()) {
            desc += 'Indikator: ' + opt.dataset.indicator;
        }
        if (opt.dataset.finding && opt.dataset.finding.trim()) {
            desc += (desc ? '\n\n' : '') + 'Temuan: ' + opt.dataset.finding;
        }
        if (opt.dataset.category && opt.dataset.category.trim()) {
            desc += (desc ? '\n' : '') + 'Kategori: ' + opt.dataset.category;
        }
        if (opt.dataset.score !== '') {
            desc += (desc ? '\n' : '') + 'Skor: ' + opt.dataset.score + '/4';
        }

        if (desc) {
            descInput.value = desc;
        }

        // Highlight border input yang terisi
        criteriaInput.classList.add('border', 'border-primary');
        descInput.classList.add('border', 'border-primary');

        // Scroll ke input criteria untuk konfirmasi visual
        criteriaInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
})();
</script>
@endpush
