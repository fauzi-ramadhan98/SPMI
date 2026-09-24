@extends('layouts.admin')

@section('title', 'Buat Siklus AMI Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-success">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.audit.cycles.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border icon-only-btn" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Buat Siklus AMI Baru</h5>
                        <p class="text-muted small mb-0">Tentukan periode dan parameter siklus Audit Mutu Internal.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.audit.cycles.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Nama Siklus AMI <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="cycleName" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required readonly placeholder="Otomatis terisi setelah memilih tahun & semester">
                        <div class="form-text"><i class="fa-solid fa-wand-magic-sparkles me-1"></i>Nama diisi otomatis dari Tahun Akademik &amp; Semester untuk menjaga konsistensi penamaan.</div>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tahun Akademik <span class="text-danger">*</span></label>
                            <select name="academic_year" id="academicYear" class="form-select form-select-lg bg-light border-0 @error('academic_year') is-invalid @enderror" required>
                                <option value="" disabled selected>-- Pilih Tahun Akademik --</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->name }}" {{ old('academic_year') == $year->name ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Semester <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-select form-select-lg bg-light border-0 @error('semester') is-invalid @enderror" required>
                                <option value="" disabled selected>Pilih Semester...</option>
                                <option value="Ganjil" {{ old('semester') == 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                                <option value="Genap" {{ old('semester') == 'Genap' ? 'selected' : '' }}>Genap</option>
                            </select>
                            @error('semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control form-control-lg bg-light border-0 @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date') }}" required>
                            <div class="form-text"><i class="fa-regular fa-calendar me-1"></i>Format: Tanggal/Bulan/Tahun (dd/mm/yyyy).</div>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control form-control-lg bg-light border-0 @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date') }}" required>
                            <div class="form-text"><i class="fa-regular fa-calendar me-1"></i>Format: Tanggal/Bulan/Tahun (dd/mm/yyyy).</div>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Status Siklus <span class="text-danger">*</span></label>
                        <div class="form-control form-control-lg bg-light border-0 d-flex align-items-center">
                            <i class="fa-solid fa-lock me-2 text-success"></i> Draft (Belum Dimulai)
                            <span class="text-muted small ms-auto"><i class="fa-solid fa-circle-info me-1"></i>Status baru selalu Draft; ubah ke Aktif/Selesai pada halaman Edit.</span>
                        </div>
                        <input type="hidden" name="status" value="draft">
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-bold text-muted small">Deskripsi / Catatan (Opsional)</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3"
                            placeholder="Tambahkan catatan atau keterangan mengenai siklus AMI ini...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.audit.cycles.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-plus-circle me-2"></i> Buat Siklus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('cycleName');
        const yearSelect = document.getElementById('academicYear');
        const semSelect = document.getElementById('semester');

        function syncName() {
            const year = yearSelect.value;
            const sem = semSelect.value;
            if (year && sem) {
                nameInput.value = 'AMI ' + year + ' - ' + sem;
            } else {
                nameInput.value = '';
            }
        }

        yearSelect.addEventListener('change', syncName);
        semSelect.addEventListener('change', syncName);

        // Repopulate nama saat ada error validasi (redirect back + old())
        syncName();
    });
</script>
@endpush
