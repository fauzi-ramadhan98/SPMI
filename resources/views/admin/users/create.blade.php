@extends('layouts.admin')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border icon-only-btn" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Tambah Pengguna Baru</h5>
                        <p class="text-muted small mb-0">Buat akun login dan tetapkan hak akses pengguna di sistem SPMI.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0 @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" required placeholder="Nama lengkap pengguna">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" required placeholder="nama@email.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">NIDN / NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nidn" class="form-control form-control-lg bg-light border-0 @error('nidn') is-invalid @enderror"
                            value="{{ old('nidn') }}" placeholder="cth: 0426088801" maxlength="20">
                        <div class="form-text"><i class="fa-solid fa-circle-info me-1"></i>Wajib diisi untuk akun berperan <strong>Auditor</strong> agar otomatis tercantum pada Surat Tugas.</div>
                        @error('nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Hak Akses / Role <span class="text-danger">*</span></label>
                        <select name="role" id="role-select" class="form-select form-select-lg bg-light border-0 @error('role') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih peran pengguna...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4" id="prodi-select-group" style="display: none;">
                        <label class="form-label fw-bold text-muted small">Program Studi <span class="text-danger">*</span></label>
                        <select name="academic_program_id" id="academic-program-select" class="form-select form-select-lg bg-light border-0 @error('academic_program_id') is-invalid @enderror">
                            <option value="" disabled selected>Pilih Program Studi...</option>
                            @foreach($academicPrograms as $program)
                                <option value="{{ $program->id }}" {{ old('academic_program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4" id="unit-select-group" style="display: none;">
                        <label class="form-label fw-bold text-muted small">Unit Kerja <span class="text-danger">*</span></label>
                        <select name="unit_id" id="unit-select" class="form-select form-select-lg bg-light border-0 @error('unit_id') is-invalid @enderror">
                            <option value="" disabled selected>Pilih Unit Kerja...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->category_label }})
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4" id="pimpinan-group" style="display: none;">
                        <label class="form-label fw-bold text-muted small">Level Pimpinan <span class="text-danger">*</span></label>
                        <select name="pimpinan_level" id="pimpinan-level-select" class="form-select form-select-lg bg-light border-0 @error('pimpinan_level') is-invalid @enderror">
                            <option value="" disabled selected>Pilih level pimpinan...</option>
                            <option value="ketua" {{ old('pimpinan_level') == 'ketua' ? 'selected' : '' }}>Ketua</option>
                            <option value="wakil" {{ old('pimpinan_level') == 'wakil' ? 'selected' : '' }}>Wakil / Pimpinan Level 2</option>
                        </select>
                        @error('pimpinan_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control form-control-lg bg-light border-0 @error('password') is-invalid @enderror"
                            required placeholder="Min. 8 karakter">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-5">
                        <label class="form-label fw-bold text-muted small">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control form-control-lg bg-light border-0"
                            required placeholder="Ulangi password yang sama">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-user-plus me-2"></i> Tambahkan Pengguna
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
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role-select');
        const prodiGroup = document.getElementById('prodi-select-group');
        const prodiSelect = document.getElementById('academic-program-select');
        const unitGroup = document.getElementById('unit-select-group');
        const unitSelect = document.getElementById('unit-select');
        const pimpinanGroup = document.getElementById('pimpinan-group');
        const pimpinanSelect = document.getElementById('pimpinan-level-select');

        function toggleExtras() {
            const role = roleSelect.value;

            prodiGroup.style.display = role === 'prodi' ? 'block' : 'none';
            prodiSelect.required = role === 'prodi';

            unitGroup.style.display = role === 'unit' ? 'block' : 'none';
            unitSelect.required = role === 'unit';

            pimpinanGroup.style.display = role === 'pimpinan' ? 'block' : 'none';
            pimpinanSelect.required = role === 'pimpinan';

            if (role !== 'prodi') prodiSelect.value = '';
            if (role !== 'unit') unitSelect.value = '';
            if (role !== 'pimpinan') pimpinanSelect.value = '';
        }

        roleSelect.addEventListener('change', toggleExtras);
        toggleExtras(); // on load for old() values
    });
</script>
@endpush
