@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
<div class="card card-custom shadow-sm">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i>Audit Trail (Log Aktivitas)</h6>
        <span class="badge bg-secondary">{{ $logs->total() }} catatan</span>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari deskripsi / user / modul">
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $key => $label)
                        <option value="{{ $key }}" @selected(request('action') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua User</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-custom"><i class="fa-solid fa-filter me-1"></i>Filter</button>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary btn-custom">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle small">
                <thead class="bg-light text-muted text-uppercase">
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>Modul</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->user_name ?? $log->user?->name ?? 'Sistem' }}</td>
                        <td>
                            @if($log->action === 'created') <span class="badge bg-success-subtle text-success">Dibuat</span>
                            @elseif($log->action === 'updated') <span class="badge bg-warning-subtle text-warning">Diubah</span>
                            @else <span class="badge bg-danger-subtle text-danger">Dihapus</span>
                            @endif
                        </td>
                        <td>{{ $log->description }}</td>
                        <td><code class="small">{{ class_basename($log->model_type) }}#{{ $log->model_id }}</code></td>
                        <td class="text-muted">{{ $log->ip ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada aktivitas tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection