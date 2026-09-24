@extends('layouts.admin')

@section('title', 'Master Daftar Tilik')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Master Daftar Tilik</h4>
        <p class="text-muted mb-0 small">Butir-butir penilaian per Standar Mutu sebagai sumber generate instrumen audit</p>
    </div>
    @hasrole('spmi')
    <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahButir" id="btnTambahButir">
        <i class="fa-solid fa-plus me-1"></i> Tambah Butir
    </button>
    @endhasrole
</div>

@if($standards->count() > 0)
<div class="accordion" id="accordionChecklist">
    @foreach($standards as $std)
    <div class="card shadow-sm border-0 rounded-4 mb-3 overflow-hidden">
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4" id="heading-{{ $std->id }}">
            <button class="btn btn-link text-dark text-decoration-none d-flex align-items-center gap-2 fw-bold p-0"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $std->id }}"
                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="collapse-{{ $std->id }}">
                <i class="fa-solid fa-chevron-down text-primary small"></i>
                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">{{ $std->kode_standar ?: '—' }}</span>
                <span>{{ $std->name ?: $std->pernyataan_standar }}</span>
                @if($std->document)
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fw-normal" title="Dokumen: {{ $std->document->title }}">
                        <i class="fa-solid fa-file-lines me-1"></i>{{ $std->document->code }}@if($std->document->decree) ({{ $std->document->decree->sk_no }})@endif
                    </span>
                @endif
                <span class="badge {{ $std->type == 'IKU' ? 'bg-primary' : 'bg-info text-dark' }}">{{ $std->type }}</span>
                <span class="badge bg-light text-dark border">{{ $std->checklistItems->count() }} butir</span>
            </button>
        </div>
        <div id="collapse-{{ $std->id }}" class="collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="heading-{{ $std->id }}" data-bs-parent="#accordionChecklist">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.68rem;">
                            <tr>
                                <th class="ps-4 py-2" style="width: 55px;">Urut</th>
                                <th class="py-2" style="width: 105px;">Kode Butir</th>
                                <th class="py-2" style="width: 90px;">IKU/IKT</th>
                                <th class="py-2" style="width: 240px;">Indikator (Standar Mutu)</th>
                                <th class="py-2">Pertanyaan Audit</th>
                                <th class="py-2" style="width: 150px;">Bukti Diharapkan</th>
                                <th class="py-2 text-center" style="width: 70px;">Skor Maks</th>
                                <th class="py-2 text-center" style="width: 75px;">Status</th>
                                <th class="py-2 text-center pe-4" style="width: 130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($std->checklistItems as $item)
                            <tr class="border-bottom">
                                <td class="ps-4 py-2 text-muted">{{ $item->sort_order }}</td>
                                <td class="py-2"><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fw-bold">{{ $item->code ?: '—' }}</span></td>
                                <td class="py-2">
                                    @if($item->indicator_key)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold">{{ $item->indicator_key }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="py-2" style="line-height:1.5;">{{ $item->indicator }}</td>
                                <td class="py-2 text-muted" style="line-height:1.5;">{{ $item->audit_question ?: '—' }}</td>
                                <td class="py-2 text-muted">{{ $item->evidence_document ?: '—' }}</td>
                                <td class="py-2 text-center"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded">{{ $item->max_score }}</span></td>
                                <td class="py-2 text-center">
                                    @if($item->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size:10px;">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1" style="font-size:10px;">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-2 text-center pe-4 admin-actions-cell"><div class="admin-table-actions">
                                    @hasrole('spmi')
                                    <div class="admin-table-action-group">
                                        <button type="button" class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action"
                                                data-bs-toggle="modal" data-bs-target="#modalEditButir"
                                                data-id="{{ $item->id }}"
                                                data-url="{{ route('admin.checklist-items.update', $item->id) }}"
                                                data-standard-id="{{ $std->id }}"
                                                data-standard-name="{{ ($std->kode_standar ?: '—') . ' - ' . ($std->name ?: $std->pernyataan_standar) }}"
                                                data-code="{{ $item->code }}"
                                                data-indicator-key="{{ $item->indicator_key }}"
                                                data-audit-question="{{ $item->audit_question }}"
                                                data-rubric-4="{{ $item->rubric_4 }}"
                                                data-rubric-3="{{ $item->rubric_3 }}"
                                                data-rubric-2="{{ $item->rubric_2 }}"
                                                data-rubric-1="{{ $item->rubric_1 }}"
                                                data-evidence="{{ $item->evidence_document }}"
                                                data-max-score="{{ $item->max_score }}"
                                                data-sort="{{ $item->sort_order }}"
                                                data-active="{{ $item->is_active ? 1 : 0 }}"
                                                title="Edit" style="font-size:12px;" aria-label="Edit"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></button>
                                        <form action="{{ route('admin.checklist-items.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus butir daftar tilik ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action" title="Hapus" style="font-size:12px;" aria-label="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                    @endhasrole
                                </div></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-list-check me-2 opacity-50"></i>Belum ada butir daftar tilik untuk standar ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-3">
    {{ $standards->links() }}
</div>
@else
<div class="card shadow-sm border-0 rounded-4 p-5 text-center">
    <i class="fa-solid fa-circle-info fs-2 mb-3 text-muted opacity-50"></i>
    <p class="mb-1 fw-semibold">Belum ada Standar Mutu.</p>
    <p class="mb-0 small text-muted">Tambahkan Standar Mutu terlebih dahulu di menu <strong>Standar Mutu</strong>, lalu kelola butir daftar tiliknya di sini.</p>
</div>
@endif

{{-- Modal Tambah Butir --}}
<div class="modal fade" id="modalTambahButir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Tambah Butir Daftar Tilik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form action="{{ route('admin.checklist-items.store') }}" method="POST" id="formTambahButir">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Dokumen Mutu <span class="text-danger">*</span></label>
                        <select name="document_id" id="document_add" class="form-select" required>
                            <option value="">-- Pilih Dokumen Mutu --</option>
                            @foreach($documents as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->code }} — {{ $doc->title }}@if($doc->decree) (SK: {{ $doc->decree->sk_no }})@endif</option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilih dokumen mutu terlebih dahulu, standar akan muncul sesuai dokumen.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Standar Mutu <span class="text-danger">*</span></label>
                        <select name="quality_standard_id" id="standard_add" class="form-select" required disabled>
                            <option value="">-- Pilih Dokumen Mutu terlebih dahulu --</option>
                        </select>
                        <div id="preview_standard_add" class="form-text text-muted small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih IKU / IKT Terkait <span class="text-danger">*</span></label>
                        <select name="indicator_key" id="indicator_key_add" class="form-select" data-preview-target="preview_indicator_add" required disabled>
                            <option value="">-- Pilih Standar Mutu terlebih dahulu --</option>
                        </select>
                        <div id="preview_indicator_add" class="form-text text-primary small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pertanyaan Audit <span class="text-muted fw-normal">(Panduan Auditor)</span></label>
                        <textarea name="audit_question" class="form-control" rows="2" placeholder="misal: Apakah prodi dapat menunjukkan bukti kelulusan tepat waktu?"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Kode Butir</label>
                            <input type="text" name="code" class="form-control" placeholder="misal: S.01.B.1">
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold small">Skor Maks</label>
                            <input type="number" name="max_score" class="form-control" value="4" min="1" max="100">
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold small">Urutan</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="mb-2">
                        <label class="form-label fw-bold small mb-1">Rubrik Penilaian <span class="text-muted fw-normal">(syarat Prodi mendapat skor 1-4)</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-success bg-opacity-10 text-success">Skor 4</span></label>
                                <input type="text" name="rubric_4" class="form-control form-control-sm" placeholder="misal: Target tercapai 100% dan ada dokumen pelampauan">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-info bg-opacity-10 text-info">Skor 3</span></label>
                                <input type="text" name="rubric_3" class="form-control form-control-sm" placeholder="misal: Target tercapai 100% sesuai standar">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-warning bg-opacity-10 text-warning">Skor 2</span></label>
                                <input type="text" name="rubric_2" class="form-control form-control-sm" placeholder="misal: Target tercapai sebagian, dokumen tidak lengkap">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-danger bg-opacity-10 text-danger">Skor 1</span></label>
                                <input type="text" name="rubric_1" class="form-control form-control-sm" placeholder="misal: Target tidak tercapai">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Target Dokumen Bukti</label>
                        <input type="text" name="evidence_document" class="form-control" placeholder="misal: BAP Sidang Skripsi, SK Yudisium">
                        <div class="form-text small">Panduan bagi Prodi (Auditee) dalam menyiapkan dokumen sebelum visitasi.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active_add" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active_add">Aktif (digunakan saat generate instrumen)</label>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fa-solid fa-save me-2"></i>Simpan</button>
                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Butir --}}
<div class="modal fade" id="modalEditButir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Edit Butir Daftar Tilik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form action="" method="POST" id="formEditButir">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Standar Mutu</label>
                        <input type="text" id="edit_standard_label" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">IKU / IKT Terkait <span class="text-danger">*</span></label>
                        <select name="indicator_key" id="indicator_key_edit" class="form-select" data-preview-target="preview_indicator_edit" required>
                            <option value="">-- Pilih IKU / IKT --</option>
                        </select>
                        <div id="preview_indicator_edit" class="form-text text-primary small mt-1"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pertanyaan Audit <span class="text-muted fw-normal">(Panduan Auditor)</span></label>
                        <textarea name="audit_question" id="edit_audit_question" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Kode Butir</label>
                            <input type="text" name="code" id="edit_code" class="form-control">
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold small">Skor Maks</label>
                            <input type="number" name="max_score" id="edit_max_score" class="form-control" min="1" max="100">
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold small">Urutan</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="0">
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="mb-2">
                        <label class="form-label fw-bold small mb-1">Rubrik Penilaian <span class="text-muted fw-normal">(syarat Prodi mendapat skor 1-4)</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-success bg-opacity-10 text-success">Skor 4</span></label>
                                <input type="text" name="rubric_4" id="edit_rubric_4" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-info bg-opacity-10 text-info">Skor 3</span></label>
                                <input type="text" name="rubric_3" id="edit_rubric_3" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-warning bg-opacity-10 text-warning">Skor 2</span></label>
                                <input type="text" name="rubric_2" id="edit_rubric_2" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-1"><span class="badge bg-danger bg-opacity-10 text-danger">Skor 1</span></label>
                                <input type="text" name="rubric_1" id="edit_rubric_1" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Target Dokumen Bukti</label>
                        <input type="text" name="evidence_document" id="edit_evidence" class="form-control">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">Aktif (digunakan saat generate instrumen)</label>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm text-dark"><i class="fa-solid fa-save me-2"></i>Perbarui</button>
                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CHECKLIST_STANDARDS = @json($standardIndicators);

function findStandard(id) {
    return CHECKLIST_STANDARDS.find(function (s) {
        return String(s.id) === String(id);
    });
}

function populateStandardSelect(selectEl, documentId, selectedStdId) {
    selectEl.innerHTML = '<option value="">-- Pilih Standar Mutu --</option>';
    if (!documentId) {
        selectEl.disabled = true;
        selectEl.innerHTML = '<option value="">-- Pilih Dokumen Mutu terlebih dahulu --</option>';
        return;
    }
    var filtered = CHECKLIST_STANDARDS.filter(function (s) {
        return String(s.document_id) === String(documentId);
    });
    if (!filtered.length) {
        selectEl.disabled = true;
        selectEl.innerHTML = '<option value="">-- Tidak ada standar untuk dokumen ini --</option>';
        return;
    }
    selectEl.disabled = false;
    filtered.forEach(function (std) {
        var opt = document.createElement('option');
        opt.value = std.id;
        var label = std.kode_standar + ' \u2014 ';
        var stmt = std.pernyataan_standar || std.name || '';
        label += stmt.length > 60 ? stmt.substring(0, 60) + '...' : (stmt || '(tanpa pernyataan)');
        opt.textContent = label;
        if (selectedStdId && String(std.id) === String(selectedStdId)) opt.selected = true;
        selectEl.appendChild(opt);
    });
}

function updateStandardPreview(selectEl, previewId) {
    var preview = document.getElementById(previewId);
    if (!preview) return;
    var opt = selectEl.selectedOptions[0];
    if (!opt || !opt.value) { preview.textContent = ''; return; }
    var std = findStandard(opt.value);
    if (!std) { preview.textContent = ''; return; }
    var full = std.pernyataan_standar || std.name || '(tanpa pernyataan)';
    preview.innerHTML = '<strong>' + std.kode_standar + '</strong> \u2014 ' + full;
}

function populateIndicatorSelect(selectEl, standardId, selectedKey) {
    selectEl.innerHTML = '<option value="">-- Pilih IKU / IKT --</option>';
    var preview = document.getElementById(selectEl.dataset.previewTarget);
    if (preview) preview.textContent = '';

    var std = findStandard(standardId);
    if (!std) {
        selectEl.disabled = true;
        if (preview) preview.textContent = 'Standar tidak ditemukan.';
        return;
    }

    if (!std.indicators.length) {
        selectEl.disabled = true;
        if (preview) preview.textContent = 'Standar ini belum memiliki IKU/IKT.';
        return;
    }

    selectEl.disabled = false;
    std.indicators.forEach(function (ind) {
        var opt = document.createElement('option');
        opt.value = ind.key;
        opt.dataset.text = ind.text;
        opt.dataset.target = ind.target || '';
        var label = ind.key + ' — ' + ind.text;
        if (ind.target) label += ' (Target: ' + ind.target + ')';
        opt.textContent = label;
        if (selectedKey && selectedKey === ind.key) opt.selected = true;
        selectEl.appendChild(opt);
    });

    updateIndicatorPreview(selectEl);
}

function updateIndicatorPreview(selectEl) {
    var preview = document.getElementById(selectEl.dataset.previewTarget);
    if (!preview) return;
    var opt = selectEl.selectedOptions[0];
    if (!opt || !opt.value) {
        preview.textContent = '';
        return;
    }
    var text = opt.dataset.text || '';
    var target = opt.dataset.target || '';
    preview.textContent = 'Indikator: ' + text + (target ? ' (Target: ' + target + ')' : '');
}

document.addEventListener('DOMContentLoaded', function() {
    var docSelectAdd = document.getElementById('document_add');
    var stdSelectAdd = document.getElementById('standard_add');
    var indSelectAdd = document.getElementById('indicator_key_add');
    var indSelectEdit = document.getElementById('indicator_key_edit');

    // Reset form tambah
    document.getElementById('btnTambahButir').addEventListener('click', function() {
        document.getElementById('formTambahButir').reset();
        document.getElementById('is_active_add').checked = true;
        stdSelectAdd.innerHTML = '<option value="">-- Pilih Dokumen Mutu terlebih dahulu --</option>';
        stdSelectAdd.disabled = true;
        indSelectAdd.innerHTML = '<option value="">-- Pilih Standar Mutu terlebih dahulu --</option>';
        indSelectAdd.disabled = true;
        var previewStd = document.getElementById('preview_standard_add');
        if (previewStd) previewStd.textContent = '';
    });

    // Cascading: Pilih Dokumen -> Isi Standar
    docSelectAdd.addEventListener('change', function() {
        populateStandardSelect(stdSelectAdd, this.value, null);
        indSelectAdd.innerHTML = '<option value="">-- Pilih Standar Mutu terlebih dahulu --</option>';
        indSelectAdd.disabled = true;
        var previewStd = document.getElementById('preview_standard_add');
        if (previewStd) previewStd.textContent = '';
    });

    // Cascading: Pilih Standar -> Isi IKU/IKT + preview
    stdSelectAdd.addEventListener('change', function() {
        populateIndicatorSelect(indSelectAdd, this.value, null);
        updateStandardPreview(stdSelectAdd, 'preview_standard_add');
    });

    indSelectAdd.addEventListener('change', function() {
        updateIndicatorPreview(this);
    });

    indSelectEdit.addEventListener('change', function() {
        updateIndicatorPreview(this);
    });

    // Modal edit — isi data
    document.querySelectorAll('[data-bs-target="#modalEditButir"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('formEditButir').action = btn.dataset.url;
            document.getElementById('edit_standard_label').value = btn.dataset.standardName || '';
            document.getElementById('edit_code').value = btn.dataset.code || '';
            populateIndicatorSelect(indSelectEdit, btn.dataset.standardId, btn.dataset.indicatorKey || '');
            document.getElementById('edit_audit_question').value = btn.dataset.auditQuestion || '';
            document.getElementById('edit_rubric_4').value = btn.dataset.rubric4 || '';
            document.getElementById('edit_rubric_3').value = btn.dataset.rubric3 || '';
            document.getElementById('edit_rubric_2').value = btn.dataset.rubric2 || '';
            document.getElementById('edit_rubric_1').value = btn.dataset.rubric1 || '';
            document.getElementById('edit_evidence').value = btn.dataset.evidence || '';
            document.getElementById('edit_max_score').value = btn.dataset.maxScore || 4;
            document.getElementById('edit_sort_order').value = btn.dataset.sort || 0;
            document.getElementById('edit_is_active').checked = btn.dataset.active === '1';
        });
    });
});
</script>
@endpush
