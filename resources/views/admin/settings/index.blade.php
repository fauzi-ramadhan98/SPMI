@extends('layouts.admin')

@section('title', 'Konfigurasi Aplikasi')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-gear me-2"></i>Konfigurasi Aplikasi</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Institusi</label>
                        <input type="text" name="institution_name" class="form-control"
                               value="{{ old('institution_name', $settings->get('institution_name')->value ?? setting('institution_name', 'STMIK Mardira Indonesia')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Singkat / Akronim</label>
                        <input type="text" name="institution_short_name" class="form-control"
                               value="{{ old('institution_short_name', setting('institution_short_name', '')) }}">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun Akademik Aktif</label>
                            <input type="text" name="academic_year" class="form-control"
                                   placeholder="contoh: 2025/2026"
                                   value="{{ old('academic_year', setting('academic_year', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Semester Aktif</label>
                            <select name="semester" class="form-select">
                                <option value="">-- Pilih --</option>
                                @foreach(['Ganjil', 'Genap'] as $sem)
                                    <option value="{{ $sem }}" @selected(setting('semester') === $sem)>{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Alamat</label>
                        <input type="text" name="address" class="form-control"
                               value="{{ old('address', setting('address', '')) }}">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Telepon</label>
                            <input type="text" name="phone" class="form-control"
                                   value="{{ old('phone', setting('phone', '')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="{{ old('email', setting('email', '')) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset(setting('logo', 'images/logo.png')) }}" alt="Logo"
                                 style="height: 50px; width: auto;" class="bg-light border rounded p-1">
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                        <div class="form-text">Kosongkan jika tidak ingin mengganti logo. Ukuran maks 2MB.</div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-file-pdf me-2"></i>Pengaturan Cetak Laporan AMI (PDF)</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Kode Dokumen (Header PDF)</label>
                            <input type="text" name="report_kode" class="form-control"
                                   placeholder="STMIKMI.LPMI.AMI.VIII.1"
                                   value="{{ old('report_kode', setting('report_kode', 'STMIKMI.LPMI.AMI.VIII.1')) }}">
                            <div class="form-text">Tampil pada baris &ldquo;Kode&rdquo; running head setiap halaman laporan.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Edisi</label>
                            <input type="text" name="report_edisi" class="form-control"
                                   placeholder="2"
                                   value="{{ old('report_edisi', setting('report_edisi', '2')) }}">
                            <div class="form-text">Tampil pada baris &ldquo;Edisi&rdquo; running head.</div>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="hidden" name="report_cover_enabled" value="0">
                        <input type="checkbox" class="form-check-input" id="reportCoverEnabled"
                               name="report_cover_enabled" value="1"
                               @checked(old('report_cover_enabled', setting('report_cover_enabled', '1')) === '1')>
                        <label class="form-check-label fw-bold" for="reportCoverEnabled">Tampilkan halaman cover pada laporan PDF</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Cover Kustom (opsional)</label>
                        @if(setting('report_cover_image'))
                            <div class="mb-2">
                                <img src="{{ Storage::disk('public')->url(setting('report_cover_image')) }}" alt="Cover Laporan"
                                     style="max-height: 170px;" class="bg-light border rounded p-1">
                            </div>
                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="report_cover_image_delete" value="1" id="coverDelete">
                                <label class="form-check-label small" for="coverDelete">Hapus cover kustom (kembali ke cover bawaan)</label>
                            </div>
                        @endif
                        <input type="file" name="report_cover_image" class="form-control" accept="image/png,image/jpeg">
                        <div class="form-text">Jika diisi, gambar ini menjadi halaman cover pertama (disarankan rasio portrait/A4, maks 5MB). Kosongkan untuk mempertahankan cover saat ini.</div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-file-signature me-2"></i>Format Kode Dokumen SPMI</h6>

                    <div class="mb-3 form-check">
                        <input type="hidden" name="document_auto_generate" value="0">
                        <input type="checkbox" class="form-check-input" id="documentAutoGenerate"
                               name="document_auto_generate" value="1"
                               @checked(old('document_auto_generate', setting('document_auto_generate', '1')) === '1')>
                        <label class="form-check-label fw-bold" for="documentAutoGenerate">Generate Kode Dokumen Otomatis</label>
                        <div class="form-text">Jika aktif, kode dokumen (Dokumen Mutu/SPMI) dibuat otomatis mengikuti template di bawah ketika petugas mengunggah dokumen baru.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Prefix Kode Dokumen</label>
                        <input type="text" name="document_code_prefix" class="form-control @error('document_code_prefix') is-invalid @enderror"
                               placeholder="STMIK-MI/SPMI"
                               value="{{ old('document_code_prefix', setting('document_code_prefix', 'STMIK-MI/SPMI')) }}">
                        @error('document_code_prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Bagian tetap di awal kode, misal <code>STMIK-MI/SPMI</code> atau <code>STMIK-MI.SPMI</code>.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Template Format Kode</label>
                        <input type="text" name="document_code_format" class="form-control @error('document_code_format') is-invalid @enderror"
                               placeholder="{prefix}/{parent_code}.{child_code}.{seq}"
                               value="{{ old('document_code_format', setting('document_code_format', '{prefix}/{parent_code}.{child_code}.{seq}')) }}">
                        @error('document_code_format')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">
                            Placeholder yang didukung: <code>{prefix}</code>, <code>{parent_code}</code>, <code>{child_code}</code>,
                            <code>{seq}</code> (nomor urut, wajib ada), <code>{MM}</code> (bulan), <code>{YYYY}</code> (tahun 4 digit), <code>{YY}</code> (tahun 2 digit).<br>
                            Contoh lain: <code>{prefix}.{parent_code}.{child_code}.{seq}</code> atau <code>{prefix}/{parent_code}.{child_code}.{seq}/{MM}.{YYYY}</code>.
                        </div>
                    </div>

                    <div class="alert alert-light border small mb-4">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Perubahan format hanya berlaku untuk kode dokumen baru yang dibuat setelah ini. Dokumen yang sudah ada tidak berubah kodenya.
                    </div>

                    <button type="submit" class="btn btn-primary btn-custom">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Konfigurasi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-circle-info me-2"></i>Informasi</h6>
            </div>
            <div class="card-body small text-muted">
                <p class="mb-2"><i class="fa-solid fa-server me-2"></i>Nilai ini dipakai secara global di seluruh halaman publik & admin.</p>
                <p class="mb-2"><i class="fa-solid fa-calendar me-2"></i>Tahun akademik & semester aktif digunakan sebagai nilai default pada form siklus AMI, survei, dan Risk Register.</p>
                <p class="mb-0"><i class="fa-solid fa-user-shield me-2"></i>Hanya Super Admin (Administrator) yang dapat mengubah konfigurasi ini.</p>
            </div>
        </div>
    </div>
</div>
@endsection