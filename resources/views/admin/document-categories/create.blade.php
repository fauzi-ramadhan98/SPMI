@extends('layouts.admin')

@section('title', 'Tambah Kategori Dokumen')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.document-categories.index', ['module' => $module]) }}" class="btn btn-sm btn-light rounded-circle me-3 border icon-only-btn" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Tambah Kategori Dokumen</h5>
                        <p class="text-muted small mb-0">Kode kategori dipakai sebagai placeholder {parent_code}/{child_code} pada format kode dokumen otomatis.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.document-categories.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="module" value="{{ $module }}">

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kode Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control form-control-lg bg-light border-0 text-uppercase @error('code') is-invalid @enderror"
                            value="{{ old('code') }}" required placeholder="Contoh: K-SKP, S-BELAJAR">
                        <div class="form-text">Kode unik, huruf kapital, dipakai pada susunan kode dokumen.</div>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required placeholder="Contoh: SK Penetapan Standar">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kategori Induk (Parent)</label>
                        <select name="parent_id" class="form-select form-select-lg bg-light border-0 @error('parent_id') is-invalid @enderror">
                            <option value="">-- Tanpa Induk (Kategori Utama/Level 1) --</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', request('parent_id')) == $parent->id ? 'selected' : '' }}>
                                    {{ str_repeat('— ', $parent->treeDepth) }}{{ $parent->name }} ({{ $parent->code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Kosongkan jika ini kategori utama (misal KEBIJAKAN, MANUAL, STANDAR, FORM-SOP). Pilih induk jika ini sub-kategori — sub-kategori juga boleh punya sub-kategori lagi (kedalaman tidak dibatasi).</div>
                        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Target Role</label>
                        <input type="text" name="target_roles" class="form-control form-control-lg bg-light border-0" value="{{ old('target_roles', 'spmi') }}" placeholder="misal: spmi, prodi, unit">
                        <div class="form-text">Opsional. Role yang berhak mengelola dokumen pada kategori ini.</div>
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input mt-0 shadow-sm me-3" type="checkbox" role="switch" id="is_active" name="is_active" value="1" style="width: 2.5rem; height: 1.25rem;" checked>
                            <div>
                                <label class="form-check-label fw-bold" for="is_active">Kategori Aktif</label>
                                <p class="text-muted small mb-0">Kategori aktif akan muncul pada form unggah dokumen.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.document-categories.index', ['module' => $module]) }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-tags me-2"></i> Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
