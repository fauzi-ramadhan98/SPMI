@extends('layouts.admin')

@section('title', 'Dashboard Sistem')

@section('content')
<div class="row g-4 mb-5">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-primary border-4 shadow-sm h-100 py-2">
            <div class="card-body" style="padding:1.25rem;">
                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total User</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $totalUsers }}</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-info border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-info text-uppercase mb-1">Program Studi</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $totalPrograms }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-success border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-success text-uppercase mb-1">Unit Kerja</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $totalUnits }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-warning border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-warning text-uppercase mb-1">Dokumen SPMI</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $totalDocuments }}</div></div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card card-custom border-start border-danger border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="text-xs fw-bold text-danger text-uppercase mb-1">Siklus Aktif</div>
                <div class="h5 mb-0 fw-bold text-dark">{{ $activeCycles }}</div></div>
        </div>
    </div>
    <div class="col-xl-1 col-md-4 col-6">
        <div class="card card-custom border-start border-secondary border-4 shadow-sm h-100 py-2">
            <div class="card-body"><div class="d-flex align-items-baseline justify-content-between">
                <span class="small fw-bold text-secondary text-uppercase">Log</span>
                <span class="h5 mb-0 fw-bold text-dark">{{ $totalLogs }}</span></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom"><h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-users me-2"></i>User per Role</h6></div>
            <div class="card-body">
                @if($usersByRole->isNotEmpty())
                <table class="table table-sm align-middle mb-0">
                    <thead class="bg-light text-muted small">
                        <tr><th>Role</th><th class="text-end">Jumlah</th></tr>
                    </thead>
                    <tbody>
                        @foreach($usersByRole as $role => $count)
                        <tr>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $role }}</span></td>
                            <td class="text-end fw-bold">{{ $count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted mb-0">Belum ada role.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-custom shadow-sm">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Aktivitas Terbaru</h6>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0 small">
                        <tbody>
                        @forelse($recentActivity as $log)
                        <tr class="border-bottom">
                            <td class="ps-4 py-2" style="white-space:nowrap;">{{ $log->created_at->diffForHumans() }}</td>
                            <td>{{ $log->user_name ?? 'Sistem' }}</td>
                            <td>
                                @if($log->action === 'created')<span class="badge bg-success-subtle text-success">Dibuat</span>
                                @elseif($log->action === 'updated')<span class="badge bg-warning-subtle text-warning">Diubah</span>
                                @else<span class="badge bg-danger-subtle text-danger">Dihapus</span>@endif
                            </td>
                            <td class="text-muted" style="max-width:300px;">{{ \Illuminate\Support\Str::limit($log->description, 60) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada aktivitas.</td></tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection