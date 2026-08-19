@extends('layouts.admin')

@section('title', 'Edit Halaman: ' . $page->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-warning">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Edit Konten Halaman</h5>
                        <p class="text-muted small mb-0">Mengedit: <code>/{{ $page->slug }}</code></p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Judul Halaman <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0 @error('title') is-invalid @enderror"
                            value="{{ old('title', $page->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Meta Deskripsi (SEO)</label>
                        <input type="text" name="meta_description" class="form-control bg-light border-0"
                            value="{{ old('meta_description', $page->meta_description) }}" placeholder="Deskripsi singkat untuk mesin pencari (maks. 160 karakter)">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Konten Halaman <span class="text-danger">*</span></label>
                        <textarea name="content" id="content" class="form-control bg-light border-0 @error('content') is-invalid @enderror"
                            rows="15" required>{{ old('content', $page->content) }}</textarea>
                        <div class="form-text text-muted small mt-1"><i class="fa-solid fa-circle-info me-1"></i> Mendukung HTML. Gunakan tag &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt; dll.</div>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <input class="form-check-input mt-0" type="checkbox" role="switch" id="is_published" name="is_published" value="1"
                                style="width: 2.5rem; height: 1.25rem;" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                            <div>
                                <label class="form-check-label fw-bold" for="is_published">Publish / Tampilkan Halaman</label>
                                <p class="text-muted small mb-0">Halaman yang dipublish akan tampil di website publik.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-warning rounded-pill px-5 fw-bold shadow-sm text-dark">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Konten
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
