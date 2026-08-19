@extends('layouts.admin')

@section('title', 'Dashboard Kinerja')

@section('content')
@php
    $highRisks = $risks->where('risk_level', 'High')->count();
    $medRisks  = $risks->where('risk_level', 'Medium')->count();
    $lowRisks  = $risks->where('risk_level', 'Low')->count();
    $submittedEvals = $evaluations->where('status', 'submitted')->count() + $evaluations->where('status', 'verified')->count();
@endphp

<div class="row g-4 mb-5">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Evaluasi Diri</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $evaluations->count() }}</div>
                <div class="small text-muted">{{ $submittedEvals }} disubmit</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-danger border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-danger text-uppercase mb-1">Risiko Tinggi</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $highRisks }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Risiko Sedang</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $medRisks }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-success text-uppercase mb-1">Risiko Rendah</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $lowRisks }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-info border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-info text-uppercase mb-1">Penugasan Audit</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $assignments->count() }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-secondary border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-secondary text-uppercase mb-1">Temuan Terbuka</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $openFindings }}</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        @if($rtmMinutes->isNotEmpty())
        <div class="card card-custom shadow-sm border-success mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center bg-success text-white">
                <h6 class="m-0 fw-bold"><i class="fa-solid fa-gavel me-2"></i>Risalah RTM Disahkan</h6>
                <span class="badge bg-white text-success rounded-pill">{{ $rtmMinutes->count() }} dokumen</span>
            </div>
            <div class="card-body p-0">
                @foreach($rtmMinutes as $rtm)
                <div class="border-bottom px-3 py-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-bold text-dark">{{ $rtm->title }}</div>
                            <div class="small text-muted">
                                <i class="fa-regular fa-calendar me-1"></i>{{ $rtm->meeting_date?->format('d M Y') }}
                                @if($rtm->approved_at) · Disahkan {{ $rtm->approved_at->format('d M Y') }} @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.rtm.pdf', $rtm->id) }}" class="btn btn-sm btn-outline-success rounded-pill">
                            <i class="fa-solid fa-file-pdf me-1"></i> Unduh
                        </a>
                    </div>
                    @foreach($rtm->instructions as $instruction)
                        @if(($instruction->academic_program_id && $instruction->academic_program_id === auth()->user()->academic_program_id)
                            || ($instruction->unit_id && $instruction->unit_id === auth()->user()->unit_id))
                        <div class="small mt-2 border-start border-3 border-success ps-3 text-muted">
                            <i class="fa-solid fa-arrow-right me-1 text-success"></i>{{ $instruction->instruction }}
                        </div>
                        @endif
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-clipboard-user me-2"></i>Status Audit Saya</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted text-uppercase">
                            <tr><th class="ps-4">Siklus</th><th>Auditor</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $a)
                            <tr>
                                <td class="ps-4 py-3">{{ $a->cycle?->name ?? '-' }}</td>
                                <td>{{ $a->auditor_name ?? '-' }}</td>
                                <td>
                                    @if($a->status === 'selesai')<span class="badge bg-success-subtle text-success">Selesai</span>
                                    @elseif($a->status === 'berlangsung')<span class="badge bg-warning-subtle text-warning">Berlangsung</span>
                                    @else<span class="badge bg-secondary-subtle text-secondary">Menunggu</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada penugasan audit.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bell me-2"></i>Notifikasi</h6>
                @if($unreadNotifications > 0)
                <span class="badge bg-danger rounded-pill">{{ $unreadNotifications }} baru</span>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($notifications as $notice)
                <div class="border-bottom px-3 py-2 {{ $notice->read_at ? 'bg-white' : 'bg-info bg-opacity-10' }}">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation mt-1 text-danger"></i>
                        <div>
                            <div class="small fw-semibold text-dark">{{ $notice->data['message'] ?? 'Notifikasi SPMI' }}</div>
                            @if(!empty($notice->data['note']))
                                <div class="small text-muted mt-1">Catatan: {{ $notice->data['note'] }}</div>
                            @endif
                            <div class="text-muted" style="font-size: 10px;">{{ $notice->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">
                    <i class="fa-solid fa-bell-slash fs-3 mb-2 opacity-25"></i>
                    <div>Belum ada notifikasi.</div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bolt me-2"></i>Aksi Cepat</h6></div>
            <div class="card-body">
                <div class="d-grid gap-3">
                    <a href="{{ route('admin.evaluations.index') }}" class="btn btn-outline-primary text-start border-2">
                        <i class="fa-solid fa-pen-ruler me-2 fa-fw"></i> Isi Evaluasi Diri (ED)
                    </a>
                    <a href="{{ route('admin.risk-registers.create') }}" class="btn btn-outline-danger text-start border-2">
                        <i class="fa-solid fa-triangle-exclamation me-2 fa-fw"></i> Isi Profil Risiko
                    </a>
                    <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-outline-info text-start border-2">
                        <i class="fa-solid fa-clipboard-check me-2 fa-fw"></i> Lihat Penugasan Audit
                    </a>
                    <a href="{{ route('admin.rtm.index') }}" class="btn btn-outline-success text-start border-2">
                        <i class="fa-solid fa-landmark me-2 fa-fw"></i> Risalah RTM (Disahkan)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection