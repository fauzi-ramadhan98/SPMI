@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
<div class="row g-4 mb-5">
    <!-- Stat Cards -->
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                            Total Dokumen SPMI</div>
                        <div class="h5 mb-0 fw-bold text-dark">{{ $totalDocuments }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-file-pdf fa-2x text-muted opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Siklus AMI Aktif</div>
                        <div class="h5 mb-0 fw-bold text-dark">{{ $activeCycles }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-rotate fa-2x text-muted opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                            Unit Kerja Aktif</div>
                        <div class="h5 mb-0 fw-bold text-dark">{{ $totalUnits }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-building fa-2x text-muted opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-info border-4 shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Auditor
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 fw-bold text-dark">{{ $totalAuditors }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-user-tie fa-2x text-muted opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                            Respons Survei</div>
                        <div class="h5 mb-0 fw-bold text-dark">{{ $totalResponses }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-comments fa-2x text-muted opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Documents (hanya administrator & spmi) -->
    @hasrole('administrator|spmi')
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Dokumen Terunggah Terbaru</h6>
                <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 rounded-start">Judul Dokumen</th>
                                <th>Kategori</th>
                                <th>Pengunggah</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDocuments as $doc)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-danger bg-opacity-10 text-danger rounded p-2 me-3">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">{{ $doc->title }}</div>
                                            @if($doc->is_public)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.7rem;">Publik</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25" style="font-size: 0.7rem;">Internal</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $doc->document_type }}</span></td>
                                <td>
                                    <span class="text-muted small">
                                        <i class="fa-regular fa-user me-1"></i> {{ $doc->uploader ? $doc->uploader->name : 'Sistem' }}
                                    </span>
                                </td>
                                <td><span class="text-muted small">{{ $doc->created_at->diffForHumans() }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada dokumen yang diunggah.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endhasrole

    <!-- Quick Actions / Info -->
    <div class="@hasrole('administrator|spmi') col-lg-4 @else col-lg-12 @endhasrole">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    @hasrole('spmi|administrator')
                    <a href="{{ route('admin.evaluations.create') }}" class="btn btn-outline-primary text-start border-2">
                        <i class="fa-solid fa-pen-ruler me-2 fa-fw"></i> Kelola Evaluasi Diri (ED)
                    </a>
                    <a href="{{ route('admin.documents.create') }}" class="btn btn-outline-primary text-start border-2">
                        <i class="fa-solid fa-cloud-arrow-up me-2 fa-fw"></i> Unggah Dokumen Baru
                    </a>
                    @endhasrole
                    
                    @hasrole('spmi')
                    <a href="{{ route('admin.surat-tugas.index') }}" class="btn btn-outline-success text-start border-2">
                        <i class="fa-solid fa-envelope-open-text me-2 fa-fw"></i> Surat Tugas & Jadwal Visitasi
                    </a>
                    <a href="{{ route('admin.audit.cycles.create') }}" class="btn btn-outline-success text-start border-2">
                        <i class="fa-solid fa-plus-circle me-2 fa-fw"></i> Buat Siklus AMI Baru
                    </a>
                    @endhasrole

                    @hasrole('prodi|unit')
                    <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-info text-start border-2">
                        <i class="fa-solid fa-pen-ruler me-2 fa-fw"></i> Isi Evaluasi Diri (ED)
                    </a>
                    @endhasrole

                    @hasrole('auditor|pimpinan')
                    <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-info text-start border-2">
                        <i class="fa-solid fa-eye me-2 fa-fw"></i> Lihat Evaluasi Diri (ED)
                    </a>
                    @endhasrole

                    @hasrole('auditor')
                    <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-outline-warning text-dark text-start border-2">
                        <i class="fa-solid fa-list-check me-2 fa-fw"></i> Lihat Tugas Audit Saya
                    </a>
                    @endhasrole

                    @hasrole('spmi|auditor')
                    <a href="{{ route('admin.kertas-kerja.index') }}" class="btn btn-outline-primary text-start border-2">
                        <i class="fa-solid fa-clipboard-list me-2 fa-fw"></i> Kertas Kerja Auditor
                    </a>
                    @endhasrole

                    @hasrole('prodi|unit')
                    <a href="{{ route('admin.risk-registers.create') }}" class="btn btn-outline-danger text-start border-2">
                        <i class="fa-solid fa-triangle-exclamation me-2 fa-fw"></i> Isi Profil Risiko
                    </a>
                    @endhasrole
                </div>
            </div>
        </div>
        
        <div class="card card-custom shadow-sm bg-primary text-white text-center p-4 mt-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;">
            <i class="fa-solid fa-rocket fs-1 mb-3 opacity-50"></i>
            <h5 class="fw-bold">SPMI Center V1.0</h5>
            <p class="small text-white-50 mb-0">Sistem terintegrasi untuk pengelolaan standar mutu, AMI, dan evaluasi berbasis data.</p>
        </div>
    </div>
</div>
@endsection
