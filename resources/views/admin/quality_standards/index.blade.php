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
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fa-solid fa-download me-1"></i> Download Template
            </button>
            <ul class="dropdown-menu shadow-sm border-0">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.quality-standards.download-template', ['format' => 'xlsx']) }}">
                        <i class="fa-regular fa-file-excel me-2 text-success"></i> Template Excel (.xlsx)
                    </a>
                </li>
            </ul>
        </div>
        {{-- Tambah Standar --}}
        @hasrole('spmi')
        <button class="btn btn-primary btn-sm px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahStandar">
            <i class="fa-solid fa-plus me-1"></i> Tambah Standar
        </button>
        @endhasrole
    </div>
</div>

{{-- Alert --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4 rounded-3 shadow-sm border-0" role="alert">
    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 shadow-sm border-0" role="alert">
    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Upload Standar Card --}}
@hasrole('spmi')
<div class="card shadow-sm border-0 rounded-4 mb-4" style="border-top: 4px solid #0d6efd!important;">
    <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4 d-flex align-items-center gap-2">
        <i class="fa-solid fa-upload text-primary"></i>
        <span class="fw-semibold">Upload Dokumen Standar</span>
        <span class="text-muted small ms-1">(PDF, Word, Excel — maks. 10 MB)</span>
    </div>
    <div class="card-body px-4 pb-4">
        <form action="{{ route('admin.quality-standards.store') }}" method="POST" enctype="multipart/form-data" id="formUploadStandar">
            @csrf
            <input type="hidden" name="type" value="IKU">
            <input type="hidden" name="pernyataan_standar" value="-">
            <div class="d-flex align-items-end gap-3 flex-wrap">
                <div class="flex-grow-1" style="max-width: 480px;">
                    <input type="file" name="file" id="fileUpload" class="form-control @error('file') is-invalid @enderror"
                        accept=".pdf,.doc,.docx,.xls,.xlsx">
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Simpan Standar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endhasrole

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
                        <th class="py-3 text-center" style="width:7%">Dokumen</th>
                        <th class="py-3 text-center" style="width:5%">Status</th>
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
                            <div class="fw-bold text-dark">{{ $std->name }}</div>
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
                        <td class="py-3 text-center">
                            @if($std->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size:10px;">Aktif</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1" style="font-size:10px;">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3 text-center pe-4">
                            @hasrole('spmi')
                            <a href="{{ route('admin.quality-standards.edit', $std->id) }}"
                               class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1 shadow-sm me-1"
                               title="Edit" style="font-size:12px;">
                                ✏️ Edit
                            </a>
                            <form action="{{ route('admin.quality-standards.destroy', $std->id) }}" method="POST"
                                  class="d-inline" onsubmit="return confirm('Hapus standar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 shadow-sm"
                                        title="Hapus" style="font-size:12px;">
                                    🗑️ Hapus
                                </button>
                            </form>
                            @endhasrole
                        </td>
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
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Kode Standar</label>
                            <input type="text" name="kode_standar" class="form-control" placeholder="misal: S.01">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Nama Standar <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="misal: Standar Pendidikan" required>
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
                        <div class="col-12">
                            <label class="form-label fw-bold small">Upload Dokumen Standar <span class="text-muted fw-normal">(PDF, Word, Excel — maks 10MB)</span></label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx">
                        </div>
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
@endsection

@push('scripts')
<script>
    function indicatorTemplate(kind, index, label) {
        const badgeClass = kind === 'iku' ? 'bg-primary' : 'bg-success';
        const isIku = kind === 'iku';
        const removeBtn = isIku
            ? '<button type="button" class="btn btn-outline-danger btn-sm remove-iku-row" title="Hapus baris ini"><i class="fa-solid fa-trash-can"></i></button>'
            : '<button type="button" class="btn btn-outline-danger btn-sm remove-ikt-row" title="Hapus baris ini"><i class="fa-solid fa-trash-can"></i></button>';
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
    });
</script>
@endpush
