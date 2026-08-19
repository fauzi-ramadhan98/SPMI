@extends('layouts.admin')

@section('title', 'Edit Standar Mutu')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('admin.quality-standards.index') }}" class="btn btn-light border rounded-pill btn-sm px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2 text-warning"></i>Edit Standar Mutu</h5>
</div>

<div class="card shadow-sm border-0 rounded-4" style="border-top: 4px solid #ffc107!important;">
    <div class="card-body p-4">
        <form action="{{ route('admin.quality-standards.update', $qualityStandard->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Kode Standar</label>
                    <input type="text" name="kode_standar" class="form-control @error('kode_standar') is-invalid @enderror"
                        placeholder="misal: S.01" value="{{ old('kode_standar', $qualityStandard->kode_standar) }}">
                    @error('kode_standar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-bold">Nama Standar <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        placeholder="misal: Standar Pendidikan" value="{{ old('name', $qualityStandard->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Pernyataan Standar <span class="text-danger">*</span></label>
                    <textarea name="pernyataan_standar" class="form-control @error('pernyataan_standar') is-invalid @enderror"
                        rows="4" placeholder="Tuliskan pernyataan standar mutu secara lengkap..." required>{{ old('pernyataan_standar', $qualityStandard->pernyataan_standar ?: $qualityStandard->name) }}</textarea>
                    @error('pernyataan_standar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Blok A: Indikator Kinerja Utama (IKU) --}}
                <div class="col-12">
                    <div class="border rounded-3 p-3">
                        <label class="form-label fw-bold mb-2">
                            <i class="fa-solid fa-star text-primary me-1"></i>Indikator Kinerja Utama (IKU)
                            <span class="text-danger">*</span> <span class="text-muted fw-normal small">(wajib minimal 1)</span>
                        </label>
                        <div id="ikuContainer">
                            @foreach($qualityStandard->ikuIndicators() as $i => $row)
                            <div class="indicator-row row g-2 align-items-center mb-2">
                                <div class="col-md-1">
                                    <span class="badge bg-primary text-white indicator-label">IKU {{ $loop->iteration }}</span>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="iku[{{ $i }}][text]" class="form-control"
                                        placeholder="Bunyi indikator" value="{{ old("iku.$i.text", $row['text'] ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="iku[{{ $i }}][target]" class="form-control"
                                        placeholder="Target, misal: ≥ 3.00" value="{{ old("iku.$i.target", $row['target'] ?? '') }}">
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-iku-row {{ $loop->first ? 'd-none' : '' }}"
                                        title="Hapus baris ini"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="btnAddIku" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-plus me-1"></i>Tambah IKU
                        </button>
                    </div>
                </div>

                {{-- Blok B: Indikator Kinerja Tambahan (IKT) --}}
                <div class="col-12">
                    <div class="border rounded-3 p-3">
                        <label class="form-label fw-bold mb-2">
                            <i class="fa-solid fa-plus text-success me-1"></i>Indikator Kinerja Tambahan (IKT)
                            <span class="text-muted fw-normal small">(opsional)</span>
                        </label>
                        <div id="iktContainer">
                            @foreach($qualityStandard->iktIndicators() as $i => $row)
                            <div class="indicator-row row g-2 align-items-center mb-2">
                                <div class="col-md-1">
                                    <span class="badge bg-success text-white indicator-label">IKT {{ $loop->iteration }}</span>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="ikt[{{ $i }}][text]" class="form-control"
                                        placeholder="Bunyi indikator" value="{{ old("ikt.$i.text", $row['text'] ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="ikt[{{ $i }}][target]" class="form-control"
                                        placeholder="Target, misal: ≥ 3.00" value="{{ old("ikt.$i.target", $row['target'] ?? '') }}">
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-ikt-row"
                                        title="Hapus baris ini"><i class="fa-solid fa-trash-can"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" id="btnAddIkt" class="btn btn-outline-success btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-plus me-1"></i>Tambah IKT
                        </button>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Rujukan</label>
                    <input type="text" name="rujukan" class="form-control @error('rujukan') is-invalid @enderror"
                        placeholder="contoh: Permendikbud No. 3 Tahun 2020, SN Dikti Pasal 5"
                        value="{{ old('rujukan', $qualityStandard->rujukan) }}">
                    @error('rujukan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Deskripsi / Keterangan Tambahan</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Keterangan tambahan (opsional)">{{ old('description', $qualityStandard->description) }}</textarea>
                </div>

                {{-- File Upload Section --}}
                <div class="col-12">
                    <label class="form-label fw-bold">Upload Dokumen Standar <span class="text-muted fw-normal small">(PDF, Word, Excel — maks 10MB)</span></label>
                    @if($qualityStandard->file_path)
                    <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-2 d-flex align-items-center gap-3" style="background-color:#eff6ff;">
                        <div>
                            @if($qualityStandard->file_type == 'excel')
                                <i class="fa-regular fa-file-excel text-success fa-lg"></i>
                            @elseif($qualityStandard->file_type == 'word')
                                <i class="fa-regular fa-file-word text-primary fa-lg"></i>
                            @else
                                <i class="fa-regular fa-file-pdf text-danger fa-lg"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ $qualityStandard->file_name }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $qualityStandard->file_type ? strtoupper($qualityStandard->file_type) : '' }}
                                @if($qualityStandard->file_size)
                                    &nbsp;·&nbsp; {{ round($qualityStandard->file_size / 1024, 1) }} KB
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.quality-standards.download-file', $qualityStandard->id) }}"
                           class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:12px;">
                            <i class="fa-solid fa-download me-1"></i> Download
                        </a>
                    </div>
                    <p class="text-muted small mb-1"><i class="fa-solid fa-info-circle me-1"></i>Upload file baru untuk mengganti dokumen yang ada.</p>
                    @endif
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror"
                        accept=".pdf,.doc,.docx,.xls,.xlsx">
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            {{ old('is_active', $qualityStandard->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label ms-1" for="is_active">Aktifkan Standar Mutu Ini</label>
                    </div>
                </div>
            </div>

            <hr class="mt-4">

            <div class="d-flex gap-2 mb-3">
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                    <i class="fa-solid fa-save me-2"></i>Update Standar
                </button>
                <a href="{{ route('admin.quality-standards.index') }}" class="btn btn-light border rounded-pill px-4">Batal</a>
            </div>
        </form>

        <hr class="my-4">

        <div class="border border-danger border-opacity-25 rounded-3 p-3 bg-danger bg-opacity-10">
            <h6 class="text-danger fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Zona Berbahaya</h6>
            <p class="text-muted small mb-2">Menghapus standar mutu tidak dapat dibatalkan. Pastikan Anda yakin sebelum melanjutkan.</p>
            <form action="{{ route('admin.quality-standards.destroy', $qualityStandard->id) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus standar mutu ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                    <i class="fa-solid fa-trash me-2"></i>Hapus Standar
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Riwayat Revisi --}}
<div class="card shadow-sm border-0 rounded-4 mt-4">
    <div class="card-header bg-transparent border-0 pt-3 pb-2 px-4 d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-primary"></i>
        <span class="fw-semibold">Riwayat Revisi Standar</span>
        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-auto">{{ $qualityStandard->versions->count() }} revisi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Versi</th>
                        <th class="py-3">Pernyataan / Kode</th>
                        <th class="py-3">Tipe</th>
                        <th class="py-3">Diubah Oleh</th>
                        <th class="py-3 pe-4">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qualityStandard->versions as $ver)
                    <tr>
                        <td class="ps-4 py-3"><span class="badge bg-dark rounded-pill px-3">v{{ $ver->version }}</span></td>
                        <td class="py-3">
                            <div class="fw-semibold">{{ $ver->snapshot['name'] ?? $ver->snapshot['pernyataan_standar'] ?? '-' }}</div>
                            <div class="text-muted small">{{ $ver->snapshot['kode_standar'] ?? '-' }}</div>
                        </td>
                        <td class="py-3">{{ $ver->snapshot['type'] ?? '-' }}</td>
                        <td class="py-3">{{ $ver->changer?->name ?? 'Sistem' }}</td>
                        <td class="py-3 pe-4 text-muted">{{ $ver->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat revisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
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

    function renumber(container, kind, label) {
        container.querySelectorAll('.indicator-row').forEach((row, i) => {
            row.querySelector('.indicator-label').textContent = `${label} ${i + 1}`;
            const text = row.querySelector(`input[name^="${kind}["][name$="[text]"]`);
            const target = row.querySelector(`input[name^="${kind}["][name$="[target]"]`);
            if (text) text.name = `${kind}[${i}][text]`;
            if (target) target.name = `${kind}[${i}][target]`;
        });
    }

    function toggleFirstRemove(container) {
        const rows = container.querySelectorAll('.indicator-row');
        rows.forEach((row, i) => {
            const btn = row.querySelector('.remove-iku-row');
            if (btn) btn.classList.toggle('d-none', i === 0);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const ikuC = document.getElementById('ikuContainer');
        const iktC = document.getElementById('iktContainer');

        document.getElementById('btnAddIku').addEventListener('click', function () {
            const idx = ikuC.querySelectorAll('.indicator-row').length;
            ikuC.insertAdjacentHTML('beforeend', indicatorTemplate('iku', idx, 'IKU ' + (idx + 1)));
            toggleFirstRemove(ikuC);
        });

        document.getElementById('btnAddIkt').addEventListener('click', function () {
            const idx = iktC.querySelectorAll('.indicator-row').length;
            iktC.insertAdjacentHTML('beforeend', indicatorTemplate('ikt', idx, 'IKT ' + (idx + 1)));
        });

        ikuC.addEventListener('click', function (e) {
            if (e.target.closest('.remove-iku-row')) {
                const row = e.target.closest('.indicator-row');
                if (ikuC.querySelectorAll('.indicator-row').length <= 1) return;
                row.remove();
                renumber(ikuC, 'iku', 'IKU');
                toggleFirstRemove(ikuC);
            }
        });

        iktC.addEventListener('click', function (e) {
            if (e.target.closest('.remove-ikt-row')) {
                e.target.closest('.indicator-row').remove();
                renumber(iktC, 'ikt', 'IKT');
            }
        });
    });
</script>
@endpush
