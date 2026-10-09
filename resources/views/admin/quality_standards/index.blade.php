@extends('layouts.admin')

@section('title', 'Manajemen Standar Mutu')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-star me-2 text-primary"></i>Standar Mutu (IKU &amp; IKT)</h4>
        <p class="text-muted mb-0 small">Penetapan standar mutu berdasarkan siklus PPEPP</p>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        {{-- Download Template --}}
        <a class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm" href="{{ route('admin.quality-standards.download-template', ['format' => 'xlsx']) }}" title="Download template Excel untuk import bulk standar mutu">
            <i class="fa-solid fa-download me-1"></i> Download Template
        </a>
        {{-- Import dari Excel --}}
        @hasrole('spmi')
        <button class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel
        </button>
        @endhasrole
        {{-- Tambah Standar --}}
        @hasrole('spmi')
        <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahStandar">
            <i class="fa-solid fa-plus me-1"></i> Tambah Standar
        </button>
        @endhasrole
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-transparent border-0 pt-3 pb-2 px-4 d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="fa-solid fa-table-list me-2 text-primary"></i>Daftar Standar Mutu</span>
        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ $standards->total() }} standar</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3" style="width:8%">Kode</th>
                        <th class="py-3" style="width:16%">Nama Standar</th>
                        <th class="py-3" style="width:20%">Pernyataan Standar</th>
                        <th class="py-3" style="width:10%">Rujukan</th>
                        <th class="py-3" style="width:24%">Indikator</th>
                        <th class="py-3 text-center" style="width:12%">Apabilitas</th>
                        <th class="py-3 text-center" style="width:7%">Dokumen</th>
                        <th class="py-3 text-center pe-4" style="width:10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $std)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">{{ $std->kode_standar ?: '-' }}</span>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">{{ $std->name ?: ($std->document->title ?? '-') }}</div>
                            @if($std->decree)
                                @hasrole('spmi|pimpinan|administrator')
                                <a href="{{ route('admin.standard-decrees.index') }}" class="badge bg-dark bg-opacity-75 text-white text-decoration-none mt-1" style="font-size:10px;" title="Lihat halaman SK Penetapan">
                                    <i class="fa-solid fa-stamp me-1"></i>SK: {{ $std->decree->sk_no }}
                                </a>
                                @else
                                <span class="badge bg-dark bg-opacity-75 text-white mt-1" style="font-size:10px;" title="SK Penetapan">
                                    <i class="fa-solid fa-stamp me-1"></i>SK: {{ $std->decree->sk_no }}
                                </span>
                                @endhasrole
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="fw-semibold text-muted" style="line-height:1.4; font-size: 0.85rem;">{{ $std->pernyataan_standar ?: '-' }}</div>
                        </td>
                        <td class="py-3 text-muted small">
                            <i class="fa-solid fa-book text-secondary me-1"></i> {{ $std->rujukan ?: '-' }}
                        </td>
                        <td class="py-3 text-muted small" style="line-height:1.5;">
                            @php
                                $ikuRows = $std->ikuIndicators();
                                $iktRows = $std->iktIndicators();
                                $hasInd = count($ikuRows) || count($iktRows);
                            @endphp
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-1 rounded-pill px-2">{{ count($ikuRows) }} IKU</span>
                            <span class="badge bg-success bg-opacity-10 text-success mb-1 rounded-pill px-2">{{ count($iktRows) }} IKT</span>
                            @if($hasInd)
                                <div class="mt-1">
                                    @foreach($ikuRows as $i => $r)
                                        <div><strong>IKU {{ $i + 1 }}:</strong> {{ $r['text'] }}{{ !empty($r['target']) ? ' <span class="text-secondary">— Target: ' . e($r['target']) . '</span>' : '' }}</div>
                                    @endforeach
                                    @foreach($iktRows as $i => $r)
                                        <div><strong>IKT {{ $i + 1 }}:</strong> {{ $r['text'] }}{{ !empty($r['target']) ? ' <span class="text-secondary">— Target: ' . e($r['target']) . '</span>' : '' }}</div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($std->applicabilities->isEmpty())
                                <span class="badge bg-success rounded-pill px-2 py-1"><i class="fa-solid fa-globe me-1"></i>Semua</span>
                            @else
                                @foreach($std->applicabilities as $app)
                                    @if($app->target_type === 'prodi' && $app->academicProgram)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-2 px-2 py-1 me-1 mb-1 d-inline-block">
                                            <i class="fa-solid fa-graduation-cap me-1"></i>{{ $app->academicProgram->degree_level }} {{ \Illuminate\Support\Str::limit($app->academicProgram->name, 12) }}
                                        </span>
                                    @elseif($app->target_type === 'unit' && $app->unit)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-2 px-2 py-1 me-1 mb-1 d-inline-block">
                                            <i class="fa-solid fa-building me-1"></i>{{ \Illuminate\Support\Str::limit($app->unit->name, 12) }}
                                        </span>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if($std->file_path)
                                @if($std->file_type == 'link')
                                    <a href="{{ $std->file_path }}" target="_blank" rel="noopener noreferrer"
                                       class="btn btn-xs btn-outline-info rounded-pill px-2 py-1 shadow-sm"
                                       title="Buka Link Dokumen" style="font-size:11px;">
                                        <i class="fa-solid fa-link"></i> Buka Link
                                    </a>
                                @else
                                    <a href="{{ route('admin.quality-standards.download-file', $std->id) }}"
                                       class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1 shadow-sm"
                                       title="Download: {{ $std->file_name }}" style="font-size:11px;">
                                        @if($std->file_type == 'excel')
                                            <i class="fa-regular fa-file-excel text-success"></i>
                                        @elseif($std->file_type == 'word')
                                            <i class="fa-regular fa-file-word text-primary"></i>
                                        @else
                                            <i class="fa-regular fa-file-pdf text-danger"></i>
                                        @endif
                                        <span class="ms-1">Unduh</span>
                                    </a>
                                @endif
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="py-3 text-center pe-4 admin-actions-cell"><div class="admin-table-actions">
                            @hasrole('spmi')
                            <a href="{{ route('admin.quality-standards.edit', $std->id) }}"
                               class="btn btn-sm btn-outline-warning icon-only-btn admin-table-action"
                               title="Edit" style="font-size:12px;" aria-label="Edit"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i></a>
                            <form action="{{ route('admin.quality-standards.destroy', $std->id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Hapus standar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger icon-only-btn admin-table-action"
                                        title="Hapus" style="font-size:12px;" aria-label="Hapus"><i aria-hidden="true" class="fa-solid fa-trash"></i></button>
                            </form>
                            @endhasrole
                        </div></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-circle-info fs-2 mb-3 opacity-25 d-block"></i>
                                <p class="mb-1 fw-semibold">Belum ada data standar mutu.</p>
                                <p class="mb-0 small">Gunakan form Upload di atas atau tombol <strong>Tambah Standar</strong> untuk menambahkan data.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $standards->links() }}
        </div>
    </div>
</div>

{{-- Modal Tambah Standar (form lengkap) --}}
<div class="modal fade" id="modalTambahStandar" tabindex="-1" aria-labelledby="modalTambahStandarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalTambahStandarLabel">
                    <i class="fa-solid fa-plus-circle me-2 text-primary"></i>Tambah Standar Mutu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <form action="{{ route('admin.quality-standards.store') }}" method="POST" enctype="multipart/form-data" id="formModalStandar">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small">Standar Mutu <span class="text-danger">*</span></label>
                            <select name="document_id" class="form-select" required>
                                <option value="" disabled selected>Pilih Standar Mutu...</option>
                                @foreach(\App\Models\Document::where('module','dokumen_mutu')->where('status','aktif')->with('decree')->orderBy('code')->get() as $doc)
                                    <option value="{{ $doc->id }}">
                                        {{ $doc->code }} — {{ $doc->title }} @if($doc->decree) (SK: {{ $doc->decree->sk_no }}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Pilih Standar Mutu (Dokumen Mutu) yang sudah Aktif.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Pernyataan Standar <span class="text-danger">*</span></label>
                            <textarea name="pernyataan_standar" class="form-control" rows="3"
                                placeholder="Tuliskan pernyataan standar mutu secara lengkap..." required></textarea>
                        </div>

                        {{-- Blok A: IKU --}}
                        <div class="col-12">
                            <div class="border rounded-3 p-2">
                                <label class="form-label fw-bold small mb-1">
                                    <i class="fa-solid fa-star text-primary me-1"></i>Indikator Kinerja Utama (IKU)
                                    <span class="text-danger">*</span> <span class="text-muted fw-normal">(wajib minimal 1)</span>
                                </label>
                                <div id="ikuContainerModal"></div>
                                <button type="button" id="btnAddIkuModal" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-plus me-1"></i>Tambah IKU
                                </button>
                            </div>
                        </div>

                        {{-- Blok B: IKT --}}
                        <div class="col-12">
                            <div class="border rounded-3 p-2">
                                <label class="form-label fw-bold small mb-1">
                                    <i class="fa-solid fa-plus text-success me-1"></i>Indikator Kinerja Tambahan (IKT)
                                    <span class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <div id="iktContainerModal"></div>
                                <button type="button" id="btnAddIktModal" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                    <i class="fa-solid fa-plus me-1"></i>Tambah IKT
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small">Rujukan</label>
                            <input type="text" name="rujukan" class="form-control"
                                placeholder="contoh: Permendikbud No. 3 Tahun 2020, SN Dikti Pasal 5">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Deskripsi / Keterangan Tambahan</label>
                            <textarea name="description" class="form-control" rows="2"
                                placeholder="Keterangan tambahan tentang standar ini (opsional)"></textarea>
                        </div>
                        <!--<div class="col-12">-->
                        <!--    <label class="form-label fw-bold small">Upload Dokumen Standar <span class="text-muted fw-normal">(PDF, Word, Excel — maks 10MB)</span></label>-->
                        <!--    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">-->
                        <!--</div>-->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active_modal" name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active_modal">Aktifkan Standar Mutu Ini</label>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-save me-2"></i>Simpan Standar
                        </button>
                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Import Excel --}}
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold" id="modalImportExcelLabel">
                    <i class="fa-solid fa-file-import me-2 text-success"></i>Import Standar dari Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="alert alert-info border-0 rounded-3 mb-3 py-2 px-3" style="background-color: #e8f4fd;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-info text-info mt-1"></i>
                        <div class="small">
                            <strong>Cara menggunakan:</strong>
                            <ol class="mb-0 mt-1 ps-3">
                                <li><strong>Pilih Standar Mutu (Document)</strong> yang akan ditautkan.</li>
                                <li>Klik <strong>"Download Template"</strong> di atas untuk mendapatkan file template Excel.</li>
                                <li>Isi data standar mutu (kode, nama, pernyataan, IKU/IKT, target) di template.</li>
                                <li>Upload file yang sudah diisi di form di bawah ini, lalu klik <strong>"Import Sekarang"</strong>.</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <form action="{{ route('admin.quality-standards.store') }}" method="POST" enctype="multipart/form-data" id="formImportExcel">
                    @csrf
                    <input type="hidden" name="type" value="IKU">
                    <input type="hidden" name="pernyataan_standar" value="-">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Standar Mutu (Document) <span class="text-danger">*</span></label>
                        <select name="document_id" class="form-select" required id="docIdImportExcel">
                            <option value="" disabled selected>Pilih Standar Mutu (Document)...</option>
                            @foreach(\App\Models\Document::where('module','dokumen_mutu')->where('status','aktif')->with('decree')->orderBy('code')->get() as $doc)
                                <option value="{{ $doc->id }}">
                                    {{ $doc->code }} — {{ $doc->title }} @if($doc->decree) (SK: {{ $doc->decree->sk_no }}) @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Semua standar dalam file Excel akan ditautkan ke Document Mutu ini.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih File Template (.xlsx / .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                            accept=".xlsx,.csv" required id="fileImportExcel">
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Format: Excel (.xlsx) atau CSV — maks. 10 MB</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm" id="btnSubmitImport">
                            <i class="fa-solid fa-file-import me-2"></i>Import Sekarang
                        </button>
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
    function indicatorTemplate(kind, index, label) {
        const badgeClass = kind === 'iku' ? 'bg-primary' : 'bg-success';
        const isIku = kind === 'iku';
        const removeBtn = isIku
            ? '<button type="button" class="btn btn-outline-danger btn-sm remove-iku-row icon-only-btn" title="Hapus baris ini" aria-label="Hapus baris ini"><i class="fa-solid fa-trash-can"></i></button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm remove-ikt-row icon-only-btn" title="Hapus baris ini" aria-label="Hapus baris ini"><i class="fa-solid fa-trash-can"></i></button>';
        return `
            <div class="indicator-row row g-2 align-items-center mb-2">
                <div class="col-md-1">
                    <span class="badge ${badgeClass} text-white indicator-label">${label}</span>
                </div>
                <div class="col-md-6">
                    <input type="text" name="${kind}[${index}][text]" class="form-control" placeholder="Bunyi indikator">
                </div>
                <div class="col-md-3">
                    <input type="text" name="${kind}[${index}][target]" class="form-control" placeholder="Target, misal: ≥ 3.00">
                </div>
                <div class="col-md-2 text-end">${removeBtn}</div>
            </div>`;
    }

    function initIndicatorArea(kind, containerId, btnId, label) {
        const container = document.getElementById(containerId);
        const btn = document.getElementById(btnId);
        if (!container || !btn) return;

        const renumber = () => {
            container.querySelectorAll('.indicator-row').forEach((row, i) => {
                row.querySelector('.indicator-label').textContent = `${label} ${i + 1}`;
                const text = row.querySelector(`input[name^="${kind}["][name$="[text]"]`);
                const target = row.querySelector(`input[name^="${kind}["][name$="[target]"]`);
                if (text) text.name = `${kind}[${i}][text]`;
                if (target) target.name = `${kind}[${i}][target]`;
            });
        };

        const toggleFirstRemove = () => {
            container.querySelectorAll('.indicator-row').forEach((row, i) => {
                const btnDel = row.querySelector('.remove-iku-row');
                if (btnDel) btnDel.classList.toggle('d-none', i === 0);
            });
        };

        btn.addEventListener('click', function () {
            const idx = container.querySelectorAll('.indicator-row').length;
            container.insertAdjacentHTML('beforeend', indicatorTemplate(kind, idx, label + ' ' + (idx + 1)));
            toggleFirstRemove();
        });

        container.addEventListener('click', function (e) {
            const delBtn = kind === 'iku' ? e.target.closest('.remove-iku-row') : e.target.closest('.remove-ikt-row');
            if (!delBtn) return;
            const row = delBtn.closest('.indicator-row');
            if (kind === 'iku' && container.querySelectorAll('.indicator-row').length <= 1) return;
            row.remove();
            renumber();
            toggleFirstRemove();
        });

        // Baris pertama otomatis untuk IKU
        if (kind === 'iku' && container.querySelectorAll('.indicator-row').length === 0) {
            container.insertAdjacentHTML('beforeend', indicatorTemplate('iku', 0, 'IKU 1'));
            toggleFirstRemove();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initIndicatorArea('iku', 'ikuContainerModal', 'btnAddIkuModal', 'IKU');
        initIndicatorArea('ikt', 'iktContainerModal', 'btnAddIktModal', 'IKT');

        // Loading state untuk form Import Excel
        const formImport = document.getElementById('formImportExcel');
        if (formImport) {
            formImport.addEventListener('submit', function () {
                const btn = document.getElementById('btnSubmitImport');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Memproses...';
                }
            });
        }
    });
</script>
@endpush
