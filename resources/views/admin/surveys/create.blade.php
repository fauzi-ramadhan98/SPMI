@extends('layouts.admin')

@section('title', 'Buat Survei Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.surveys.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border icon-only-btn" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Buat Kuesioner Survei Baru</h5>
                        <p class="text-muted small mb-0">Setelah menyimpan, Anda akan langsung diarahkan ke Survey Builder untuk menambahkan pertanyaan.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.surveys.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Judul Survei <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0 @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" required placeholder="Contoh: Survei Kepuasan Mahasiswa 2024/2025">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Deskripsi / Petunjuk Pengisian</label>
                        <textarea name="description" class="form-control form-control-lg bg-light border-0" rows="3"
                            placeholder="Jelaskan tujuan survei dan panduan pengisian untuk responden...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kategori Survei <span class="text-danger">*</span></label>
                        <select name="type" class="form-select form-select-lg bg-light border-0 @error('type') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih jenis/kategori survei...</option>
                            <option value="kepuasan_layanan" {{ old('type') == 'kepuasan_layanan' ? 'selected' : '' }}>Survei Kepuasan Layanan / Terpadu</option>
                            <option value="alumni" {{ old('type') == 'alumni' ? 'selected' : '' }}>Kepuasan Alumni / Tracer Study</option>
                            <option value="pengguna_lulusan" {{ old('type') == 'pengguna_lulusan' ? 'selected' : '' }}>Kepuasan Pengguna Lulusan / Mitra</option>
                            <option value="visi_misi" {{ old('type') == 'visi_misi' ? 'selected' : '' }}>Pemahaman Visi & Misi</option>
                            <option value="pembelajaran" {{ old('type') == 'pembelajaran' ? 'selected' : '' }}>Evaluasi Proses Pembelajaran</option>
                            <option value="lainnya" {{ old('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tanggal Dibuka (Opsional)</label>
                            <input type="date" name="start_date" class="form-control form-control-lg bg-light border-0" value="{{ old('start_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tanggal Ditutup (Opsional)</label>
                            <input type="date" name="end_date" class="form-control form-control-lg bg-light border-0" value="{{ old('end_date') }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="form-check form-switch d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 shadow-sm" type="checkbox" role="switch" id="is_anonymous" name="is_anonymous" value="1"
                                        style="width: 2.5rem; height: 1.25rem;" {{ old('is_anonymous') ? 'checked' : '' }}>
                                    <div>
                                        <label class="form-check-label fw-bold" for="is_anonymous">Mode Anonim</label>
                                        <p class="text-muted small mb-0">Identitas responden tidak dikumpulkan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="form-check form-switch d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 shadow-sm" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                                        style="width: 2.5rem; height: 1.25rem;" {{ old('is_active') ? 'checked' : '' }}>
                                    <div>
                                        <label class="form-check-label fw-bold" for="is_active">Aktifkan Saat Dibuat</label>
                                        <p class="text-muted small mb-0">Survei langsung tersedia untuk responden.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.surveys.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-arrow-right me-2"></i> Lanjut ke Survey Builder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
