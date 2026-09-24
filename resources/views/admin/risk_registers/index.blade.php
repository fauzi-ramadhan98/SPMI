@extends('layouts.admin')

@section('title', 'Manajemen Risk Register')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-danger">
    <div class="card-header-custom d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fa-solid fa-triangle-exclamation me-2 text-danger"></i>Risk Register (Profil Risiko)</h5>
            <p class="text-muted small mb-0 mt-1">Registrasi risiko seluruh Program Studi/Unit beserta status persetujuan SPMI.</p>
        </div>
        @hasrole('prodi|unit')
        <a href="{{ route('admin.risk-registers.create') }}" class="btn btn-danger btn-sm px-3 shadow-sm rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Tambah Entri Risiko
        </a>
        @endhasrole
    </div>

    {{-- Poin catatan client: penugasan pengisian RR per semester (SPMI) --}}
    @hasrole('spmi')
    <div class="mx-3 mt-3">
        <div class="card border-0 shadow-sm rounded-4 bg-warning bg-opacity-10">
            <div class="card-body py-3 px-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-user-check me-2 text-warning"></i>Penugasan Pengisian Risk Register (per Semester)</h6>
                        <div class="text-muted small">Prodi wajib isi Risk Register setiap semester — penugasan dibuat oleh SPMI di sini.</div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAssignRR">
                        <i class="fa-solid fa-paper-plane me-1"></i> Tugaskan Pengisian
                    </button>
                </div>
                @if($assignments->isEmpty())
                    <div class="small text-muted mb-0"><i class="fa-solid fa-circle-info me-1"></i>Belum ada penugasan. Klik "Tugaskan Pengisian" untuk periode semester berjalan.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-white mb-0 align-middle small">
                        <thead class="table-light">
                            <tr><th>Periode</th><th>Risk Owner</th><th>Catatan SPMI</th><th class="text-center">Status Isi</th><th class="text-center">Aksi</th></tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $as)
                            <tr>
                                <td class="text-nowrap">{{ $as->academic_year }}<br><strong>{{ $as->semester }}</strong></td>
                                <td>
                                    <span class="badge {{ $as->academic_program_id ? 'bg-info bg-opacity-10 text-info border' : 'bg-secondary bg-opacity-10 text-secondary border' }}">{{ $as->owner_type_label }}</span>
                                    {{ $as->owner_label }}
                                </td>
                                <td class="text-muted">{{ $as->note ? \Illuminate\Support\Str::limit($as->note, 70) : '—' }}</td>
                                <td class="text-center">
                                    @if($as->isFilled())
                                        <span class="badge bg-success rounded-pill"><i class="fa-solid fa-check me-1"></i>Sudah diisi</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill"><i class="fa-solid fa-hourglass-half me-1"></i>Belum diisi</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('admin.risk-registers.assignments.destroy', $as->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cabut penugasan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn" title="Cabut Penugasan" aria-label="Cabut Penugasan"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endhasrole

    {{-- Penugasan milik saya (Prodi / Unit) --}}
    @hasrole('prodi|unit')
    @if($myAssignment)
    <div class="mx-3 mt-3">
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2 mb-0 rounded-3 shadow-sm" role="alert">
            <div>
                <i class="fa-solid fa-thumbtack me-2"></i>
                <strong>Ditugaskan SPMI:</strong> Isi Risk Register Semester <strong>{{ $myAssignment->semester }}</strong> T.A. <strong>{{ $myAssignment->academic_year }}</strong>
                @if($myAssignment->note)
                    <div class="small mt-1 mb-0"><i class="fa-solid fa-message me-1"></i>Catatan SPMI: {{ $myAssignment->note }}</div>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($myAssignment->isFilled())
                    <span class="badge bg-success rounded-pill px-3 py-2"><i class="fa-solid fa-check me-1"></i>Sudah diisi</span>
                @else
                    <span class="badge bg-danger rounded-pill px-3 py-2"><i class="fa-solid fa-clock me-1"></i>Belum diisi</span>
                @endif
                <a href="{{ route('admin.risk-registers.create') }}" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm"><i class="fa-solid fa-plus me-1"></i>Isi Sekarang</a>
            </div>
        </div>
    </div>
    @endif
    @endhasrole

    {{-- Filter toolbar --}}
    @hasrole('spmi|pimpinan|auditor')
    <form method="GET" action="{{ route('admin.risk-registers.index') }}" class="row g-2 align-items-end mx-3 mb-3">
        <div class="col-auto">
            <label class="form-label small fw-bold mb-1 text-muted">Tahun Akademik</label>
            <select name="academic_year" class="form-select form-select-sm" style="min-width: 160px;">
                <option value="">Semua Tahun</option>
                @foreach($yearOptions as $y)
                    <option value="{{ $y }}" {{ ($filters['academic_year'] ?? null) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small fw-bold mb-1 text-muted">Semester</label>
            <select name="semester" class="form-select form-select-sm" style="min-width: 130px;">
                <option value="">Semua Semester</option>
                <option value="Ganjil" {{ ($filters['semester'] ?? null) == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                <option value="Genap" {{ ($filters['semester'] ?? null) == 'Genap' ? 'selected' : '' }}>Genap</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small fw-bold mb-1 text-muted">Program Studi</label>
            <select name="academic_program_id" class="form-select form-select-sm" style="min-width: 220px;">
                <option value="">Semua Prodi</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" {{ ($filters['academic_program_id'] ?? null) == $p->id ? 'selected' : '' }}>
                        {{ $p->degree_level }} {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small fw-bold mb-1 text-muted">Kategori Risiko</label>
            <select name="risk_category" class="form-select form-select-sm" style="min-width: 150px;">
                <option value="">Semua Kategori</option>
                <option value="Operasional" {{ ($filters['risk_category'] ?? null) == 'Operasional' ? 'selected' : '' }}>Operasional</option>
                <option value="SDM" {{ ($filters['risk_category'] ?? null) == 'SDM' ? 'selected' : '' }}>SDM</option>
                <option value="Keuangan" {{ ($filters['risk_category'] ?? null) == 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                <option value="Teknologi" {{ ($filters['risk_category'] ?? null) == 'Teknologi' ? 'selected' : '' }}>Teknologi</option>
                <option value="Kepatuhan" {{ ($filters['risk_category'] ?? null) == 'Kepatuhan' ? 'selected' : '' }}>Kepatuhan</option>
                <option value="Reputasi" {{ ($filters['risk_category'] ?? null) == 'Reputasi' ? 'selected' : '' }}>Reputasi</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small fw-bold mb-1 text-muted">Level Risiko</label>
            <select name="risk_level" class="form-select form-select-sm" style="min-width: 140px;">
                <option value="">Semua Level</option>
                <option value="High" {{ ($filters['risk_level'] ?? null) == 'High' ? 'selected' : '' }}>🔴 Tinggi</option>
                <option value="Medium" {{ ($filters['risk_level'] ?? null) == 'Medium' ? 'selected' : '' }}>🟡 Sedang</option>
                <option value="Low" {{ ($filters['risk_level'] ?? null) == 'Low' ? 'selected' : '' }}>🟢 Rendah</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-filter me-1"></i> Terapkan
            </button>
            <a href="{{ route('admin.risk-registers.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border">Reset</a>
        </div>
    </form>
    @endhasrole

    <div class="card-body p-0">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-4 mt-4 mb-0 rounded-3 shadow-sm border-0" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="table-responsive mt-3">
            <table class="table table-hover table-bordered align-middle mb-0 small">
                <thead class="table-dark text-center text-uppercase" style="font-size: 0.72rem;">
                    <tr>
                        <th class="py-3 px-3" style="width: 4%;">No</th>
                        <th class="py-3" style="width: 10%;">Standar</th>
                        <th class="py-3 text-center" style="width: 8%;">Kategori</th>
                        <th class="py-3" style="width: 16%;">Indikator</th>
                        <th class="py-3" style="width: 13%;">Kondisi Saat Ini</th>
                        <th class="py-3" style="width: 13%;">Risiko</th>
                        <th class="py-3 text-center" style="width: 6%;">Impact<br>(1-5)</th>
                        <th class="py-3 text-center" style="width: 7%;">Likelihood<br>(1-5)</th>
                        <th class="py-3 text-center" style="width: 7%;">Skor</th>
                        <th class="py-3 text-center" style="width: 8%;">Level</th>
                        <th class="py-3" style="width: 11%;">Mitigasi</th>
                        <th class="py-3 text-center" style="width: 5%;">Bukti</th>
                        <th class="py-3 text-center" style="width: 10%;">Status Persetujuan</th>
                        <th class="py-3 text-center" style="width: 8%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($risks as $i => $risk)
                    <tr class="border-bottom align-top">
                        <td class="px-3 py-3 text-center text-muted fw-bold">{{ ($risks->currentPage()-1) * $risks->perPage() + $loop->iteration }}</td>
                        <td class="py-3 px-2">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-2 px-2 py-1 d-block text-wrap text-start">
                                {{ $risk->standar_mutu ?? '—' }}
                            </span>
                            <div class="text-muted mt-1" style="font-size: 0.7rem;">{{ $risk->academic_year }}{{ $risk->semester ? ' • ' . $risk->semester : '' }} &bull; {{ $risk->owner_label }}</div>
                        </td>
                        <td class="py-3 text-center px-2">
                            @if($risk->risk_category)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-2 px-2 py-1 d-block text-wrap text-start">
                                    {{ $risk->risk_category }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-2" style="white-space: pre-wrap; max-width: 190px;">
                            <span class="text-dark">{{ $risk->butir_tilik ?? '—' }}</span>
                        </td>
                        <td class="py-3 px-2" style="white-space: pre-wrap; max-width: 170px;">
                            <span class="text-dark">{{ $risk->temuan ?? '—' }}</span>
                        </td>
                        <td class="py-3 px-2" style="white-space: pre-wrap; max-width: 170px;">
                            <span class="fw-semibold text-dark">{{ $risk->risk_description }}</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded fs-6 px-2">{{ $risk->impact }}</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded fs-6 px-2">{{ $risk->probability }}</span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge fs-6 fw-bold rounded px-3 py-2
                                @if($risk->risk_level == 'High') bg-danger
                                @elseif($risk->risk_level == 'Medium') bg-warning text-dark
                                @else bg-success @endif">
                                {{ $risk->risk_score }}
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            @if($risk->risk_level == 'High')
                                <span class="badge bg-danger rounded-pill px-2 py-1 fw-bold shadow-sm">🔴 Tinggi</span>
                            @elseif($risk->risk_level == 'Medium')
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fw-bold shadow-sm">🟡 Sedang</span>
                            @else
                                <span class="badge bg-success rounded-pill px-2 py-1 fw-bold shadow-sm">🟢 Rendah</span>
                            @endif
                        </td>
                        <td class="py-3 px-2" style="white-space: pre-wrap; max-width: 140px;">
                            <span class="text-muted">{{ $risk->mitigation_plan ?? '—' }}</span>
                        </td>
                        <td class="py-3 text-center px-2">
                            @if($risk->document_link)
                                <a href="{{ $risk->document_link }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0 shadow-sm" style="font-size: 0.65rem;" title="Lihat Bukti GDrive">
                                    <i class="fa-brands fa-google-drive me-1"></i>Link
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-center px-2" style="min-width: 120px;">
                            @if($risk->status == 'approved')
                                <span class="badge bg-success rounded-pill px-2 py-1 fw-bold shadow-sm"><i class="fa-solid fa-check me-1"></i>Disetujui</span>
                            @elseif($risk->status == 'revision')
                                <span class="badge bg-danger rounded-pill px-2 py-1 fw-bold shadow-sm">Perlu Revisi</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fw-bold shadow-sm">Menunggu Review</span>
                            @endif
                            @if($risk->status_note)
                                <div class="text-muted mt-1" style="font-size: 10px;" title="{{ $risk->status_note }}">
                                    <i class="fa-solid fa-message me-1"></i>{{ mb_strimwidth($risk->status_note, 0, 38, '…') }}
                                </div>
                            @endif
                            @if($risk->validatedBy && $risk->status !== 'pending')
                                <div class="text-muted mt-1" style="font-size: 10px;">oleh {{ $risk->validatedBy->name }}</div>
                            @endif
                        </td>
                        <td class="py-3 text-center px-2 admin-actions-cell"><div class="admin-table-actions">
                            @hasrole('spmi')
                            <div class="admin-table-action-group">
                                @if($risk->status !== 'approved')
                                <form action="{{ route('admin.risk-registers.validate', $risk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui mitigasi profil risiko ini? Status akan menjadi Disetujui.');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success icon-only-btn admin-table-action" title="Validasi / Setujui" aria-label="Validasi / Setujui"><i aria-hidden="true" class="fa-solid fa-check"></i></button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action"
                                        title="Beri Catatan Revisi"
                                        data-action-url="{{ route('admin.risk-registers.note', $risk->id) }}"
                                        data-bs-toggle="modal" data-bs-target="#modalNoteRevisi" aria-label="Beri Catatan Revisi"><i aria-hidden="true" class="fa-solid fa-message"></i></button>
                            </div>
                            @endif
                            @if(auth()->user()->hasRole('prodi|unit'))
                            <div class="admin-table-action-group">
                                <a href="{{ route('admin.risk-registers.edit', $risk->id) }}"
                                    class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action" title="Edit Profil Risiko" aria-label="Edit Profil Risiko"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.risk-registers.destroy', $risk->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus profil risiko ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" title="Hapus Profil Risiko" aria-label="Hapus Profil Risiko"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                            @else
                            @if(!auth()->user()->hasRole('spmi'))
                            <span class="text-muted small"><i class="fa-solid fa-eye me-1"></i>Read-only</span>
                            @endif
                            @endhasrole
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-shield-halved fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada profil risiko yang terdaftar.</p>
                                @hasrole('prodi|unit')
                                <a href="{{ route('admin.risk-registers.create') }}" class="btn btn-danger btn-sm mt-3 rounded-pill px-4">
                                    <i class="fa-solid fa-plus me-1"></i> Tambah Sekarang
                                </a>
                                @endhasrole
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $risks->links() }}
        </div>
    </div>
</div>

{{-- Modal Beri Catatan Revisi --}}
<div class="modal fade" id="modalNoteRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-message me-2 text-info"></i>Beri Catatan Revisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form method="POST" id="formNoteRevisi">
                    @csrf
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 rounded-3 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Catatan ini dikirim sebagai notifikasi ke dasbor <strong>Risk Owner</strong> (Prodi/Unit) dan status risiko berubah menjadi <strong>Perlu Revisi</strong>.
                    </div>
                    <textarea name="note" class="form-control" rows="3" placeholder="Tuliskan catatan / hal yang perlu diperbaiki..." required></textarea>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-info rounded-pill px-4 shadow-sm text-white fw-bold">
                            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Catatan
                        </button>
                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{-- Modal Penugasan Pengisian Risk Register (khusus SPMI) --}}
@hasrole('spmi')
<div class="modal fade" id="modalAssignRR" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-check me-2 text-danger"></i>Tugaskan Pengisian Risk Register</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body px-4">
                <form method="POST" action="{{ route('admin.risk-registers.assignments.store') }}">
                    @csrf
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 rounded-3 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Penugasan dikirim sebagai notifikasi ke dashboard <strong>Kaprodi / Unit</strong> terkait dan tampil sebagai banner pada halaman Risk Register.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tahun Akademik <span class="text-danger">*</span></label>
                            <select name="academic_year" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->name }}" {{ old('academic_year') == $year->name ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_year')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Semester <span class="text-danger">*</span></label>
                            <select name="semester" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            @error('semester')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Ditugaskan Kepada <span class="text-danger">*</span></label>
                            <select name="target" id="assignTarget" class="form-select" required>
                                <option value="semua" {{ old('target') == 'semua' ? 'selected' : '' }}>Semua Prodi &amp; Unit</option>
                                <option value="prodi" {{ old('target') == 'prodi' ? 'selected' : '' }}>Program Studi tertentu</option>
                                <option value="unit" {{ old('target') == 'unit' ? 'selected' : '' }}>Unit Kerja tertentu</option>
                            </select>
                        </div>
                        <div class="col-12" id="assignProgramCol" style="display: none;">
                            <label class="form-label fw-bold small">Program Studi <span class="text-danger">*</span></label>
                            <select name="academic_program_id" id="assignProgram" class="form-select">
                                <option value="">-- Pilih Prodi --</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}" {{ old('academic_program_id') == $p->id ? 'selected' : '' }}>{{ $p->degree_level }} {{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_program_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12" id="assignUnitCol" style="display: none;">
                            <label class="form-label fw-bold small">Unit Kerja <span class="text-danger">*</span></label>
                            <select name="unit_id" id="assignUnit" class="form-select">
                                <option value="">-- Pilih Unit --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->category_label }})</option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Catatan SPMI <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea name="note" class="form-control" rows="2" placeholder="cth: Lengkapi minimal 3 entris risiko tiap standar.">{{ old('note') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold"><i class="fa-solid fa-paper-plane me-2"></i>Kirim Penugasan</button>
                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endhasrole

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle field target pada modal Penugasan Pengisian Risk Register
    const assignTarget = document.getElementById('assignTarget');
    function syncAssignTarget() {
        if (!assignTarget) return;
        const progCol = document.getElementById('assignProgramCol');
        const unitCol = document.getElementById('assignUnitCol');
        const progSel = document.getElementById('assignProgram');
        const unitSel = document.getElementById('assignUnit');
        progCol.style.display = assignTarget.value === 'prodi' ? '' : 'none';
        unitCol.style.display = assignTarget.value === 'unit' ? '' : 'none';
        progSel.required = assignTarget.value === 'prodi';
        unitSel.required = assignTarget.value === 'unit';
    }
    if (assignTarget) {
        assignTarget.addEventListener('change', syncAssignTarget);
        syncAssignTarget();
    }

    // Isi action form catatan revisi dari tombol per baris
    document.querySelectorAll('[data-bs-target="#modalNoteRevisi"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('formNoteRevisi').action = btn.dataset.actionUrl;
        });
    });
});
</script>
@endpush