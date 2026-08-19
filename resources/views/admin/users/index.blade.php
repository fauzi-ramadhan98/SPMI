@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
    <div class="card-header-custom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">Daftar Pengguna Sistem</h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Nama Lengkap</th>
                        <th class="py-3">Email Pengguna</th>
                        <th class="py-3">Hak Akses / Role</th>
                        <th class="py-3">Waktu Bergabung</th>
                        <th class="py-3 text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="border-bottom">
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm fw-bold" style="width: 40px; height: 40px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                            </div>
                        </td>
                        <td class="py-3 text-muted">{{ $user->email }}</td>
                        <td class="py-3">
                            @foreach($user->roles as $role)
                                @if($role->name == 'administrator')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-crown me-1"></i> Administrator</span>
                                @elseif($role->name == 'spmi')
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-shield-halved me-1"></i> SPMI</span>
                                @elseif($role->name == 'pimpinan')
                                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-gem me-1"></i> Pimpinan
                                        @if($user->pimpinan_level == 'ketua') <span class="text-muted small">(Ketua)</span>@elseif($user->pimpinan_level == 'wakil') <span class="text-muted small">(Wakil)</span>@endif
                                    </span>
                                @elseif($role->name == 'auditor')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-glasses me-1"></i> Auditor</span>
                                @elseif($role->name == 'prodi')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-building-columns me-1"></i> Prodi</span>
                                @elseif($role->name == 'unit')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1"><i class="fa-solid fa-building me-1"></i> Unit</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-1">{{ $role->name }}</span>
                                @endif
                            @endforeach
                            @if($user->academicProgram)
                                <div class="small text-muted mt-1"><i class="fa-solid fa-building-columns me-1"></i>{{ $user->academicProgram->name }}</div>
                            @elseif($user->unit)
                                <div class="small text-muted mt-1"><i class="fa-solid fa-building me-1"></i>{{ $user->unit->name }}</div>
                            @endif
                        </td>
                        <td class="py-3 text-muted small"><i class="fa-regular fa-calendar me-1"></i> {{ $user->created_at->format('d M Y') }}</td>
                        <td class="py-3 text-center pe-4">
                            <div class="btn-group shadow-sm border rounded p-1 btn-group-sm">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-light text-warning border-0" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light text-danger border-0" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-users-slash fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada data pengguna yang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
