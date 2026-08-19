@extends('layouts.admin')

@section('title', 'Buat Evaluasi Diri')

@section('content')
<div class="mb-4">
    <h1 class="h4 mb-1">Buat Evaluasi Diri</h1>
    <p class="text-muted mb-0">
        @role('prodi')Evaluasi Diri untuk Program Studi: <strong>{{ auth()->user()->academicProgram->name ?? '' }}</strong>@endrole
        @role('unit')Evaluasi Diri Layanan untuk Unit: <strong>{{ auth()->user()->unit->name ?? '' }}</strong>@endrole
        (Target otomatis mengikuti akun Anda.)
    </p>
</div>

<div class="row">
    <div class="col-lg-6">
        <form action="{{ route('admin.evaluations.store') }}" method="POST" class="card">
            @csrf
            <div class="card-body">
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Nama Evaluasi (opsional)</label>
                    <input type="text" name="name" class="form-control" placeholder="Otomatis bila dikosongkan">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tahun Akademik</label>
                        <input type="text" name="academic_year" class="form-control" placeholder="cth: 2025/2026">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <select name="semester" class="form-select">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                            <option value="Tahunan">Tahunan</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary"><i class="fa-solid fa-play"></i> Lanjut & Isi Indikator</button>
            </div>
        </form>
    </div>
</div>
@endsection