@extends('layouts.admin')

@section('title', 'Tambah Halaman Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Buat Halaman Statis Baru</h5>
                        <p class="text-muted small mb-0">Tambahkan halaman informasi baru di website publik SPMI.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.pages.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Judul Halaman <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0 @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" required placeholder="Contoh: Visi dan Misi SPMI">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">URL Slug <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 text-muted">/</span>
                                <input type="text" name="slug" class="form-control form-control-lg bg-light border-0 @error('slug') is-invalid @enderror"
                                    value="{{ old('slug') }}" required placeholder="visi-misi-spmi">
                            </div>
                            <div class="form-text text-muted small">Gunakan huruf kecil, angka, dan tanda hubung saja.</div>
                            @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Meta Deskripsi (SEO)</label>
                            <input type="text" name="meta_description" class="form-control form-control-lg bg-light border-0"
                                value="{{ old('meta_description') }}" placeholder="Deskripsi singkat untuk mesin pencari...">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Konten Halaman <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control bg-light border-0 @error('content') is-invalid @enderror"
                            rows="12" required placeholder="Masukkan konten dalam format HTML...">{{ old('content') }}</textarea>
                        <div class="form-text text-muted small mt-1"><i class="fa-solid fa-circle-info me-1"></i> Mendukung tag HTML seperti &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt;, &lt;em&gt; dll.</div>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0" type="checkbox" role="switch" id="is_published" name="is_published" value="1"
                                style="width: 2.5rem; height: 1.25rem;" {{ old('is_published') ? 'checked' : '' }}>
                            <div>
                                <label class="form-check-label fw-bold" for="is_published">Publish Sekarang</label>
                                <p class="text-muted small mb-0">Halaman akan langsung tampil di website publik setelah disimpan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-plus me-2"></i> Buat Halaman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
