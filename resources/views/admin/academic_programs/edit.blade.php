@extends('layouts.admin')

@section('title', 'Edit Program Studi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header-custom border-0 bg-white pt-4 pb-0">
                <div class="d-flex align-items-center mb-3">
                    <a href="{{ route('admin.academic_programs.index') }}" class="btn btn-sm btn-light rounded-circle me-3 icon-only-btn" title="Kembali" aria-label="Kembali">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <div>
                        <h5 class="mb-0 fw-bold">Edit Program Studi</h5>
                        <p class="text-muted small mb-0">{{$academicProgram->degree_level}} {{$academicProgram->name}}</p>
                    </div>
                </div>
            </div>

            <div class="card-body p-4 p-md-5 pt-3">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{$error}}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.academic_programs.update', $academicProgram->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-4">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold">Nama Program Studi <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $academicProgram->name) }}" class="form-control @error('name') is-invalid @enderror" required placeholder="Contoh: Teknik Informatika">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-8">
                            <label for="faculty" class="form-label fw-semibold">Instansi / Institusi</label>
                            <input type="text" id="faculty" name="faculty" value="{{ old('faculty', $academicProgram->faculty) }}" class="form-control @error('faculty') is-invalid @enderror" placeholder="Contoh: STMIK Mardira Indonesia">
                            @error('faculty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="degree_level" class="form-label fw-semibold">Jenjang <span class="text-danger">*</span></label>
                            <select id="degree_level" name="degree_level" class="form-select @error('degree_level') is-invalid @enderror" required>
                                @foreach(['D3','S1','S2','S3','Profesi'] as $level)
                                    <option value="{{$level}}" {{ old('degree_level', $academicProgram->degree_level) == $level ? 'selected' : '' }}>{{$level}}</option>
                                @endforeach
                            </select>
                            @error('degree_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="accreditation" class="form-label fw-semibold">Akreditasi</label>
                            <input type="text" id="accreditation" name="accreditation" value="{{ old('accreditation', $academicProgram->accreditation) }}" class="form-control @error('accreditation') is-invalid @enderror" placeholder="Contoh: Baik Sekali">
                            @error('accreditation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-block">Status</label>
                            <div class="form-check form-switch p-3 bg-light rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $academicProgram->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Program Studi Aktif</label>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label for="head_name" class="form-label fw-semibold"><i class="fa-solid fa-user-tie me-1 text-primary"></i> Nama Ketua Program Studi</label>
                            <input type="text" id="head_name" name="head_name" value="{{ old('head_name', $academicProgram->head_name) }}" class="form-control @error('head_name') is-invalid @enderror" placeholder="Contoh: Dr. Nama Ketua, M.T.">
                            @error('head_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="head_nidn" class="form-label fw-semibold"><i class="fa-solid fa-id-card me-1 text-primary"></i> NIDN / NIK Ketua Prodi</label>
                            <input type="text" id="head_nidn" name="head_nidn" value="{{ old('head_nidn', $academicProgram->head_nidn) }}" class="form-control @error('head_nidn') is-invalid @enderror" placeholder="Contoh: 0412345678">
                            @error('head_nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <div class="form-text text-muted"><i class="fa-solid fa-circle-info me-1"></i> Nama dan NIDN Ketua Prodi akan muncul sebagai Auditee pada laporan PDF hasil audit.</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5">
                        <a href="{{ route('admin.academic_programs.index') }}" class="btn btn-light rounded-pill px-4 fw-semibold border me-md-2">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
