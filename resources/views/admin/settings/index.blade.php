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