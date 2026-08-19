@extends('layouts.admin')

@section('title', 'Manajemen Kuesioner Survei')

@section('content')
<div class="card card-custom shadow-sm mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center bg-white border-0 pt-4 pb-0">
        <div>
            <h5 class="mb-0 fw-bold">Daftar Survei & Kuesioner</h5>
            <p class="text-muted small mb-0 mt-1">Buat, desain form, dan pantau respons kepuasan pelayanan secara real-time.</p>
        </div>
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary fw-semibold rounded-pill px-4 shadow-sm">
            <i class="fa-solid fa-square-plus me-1"></i> Buat Survei Baru
        </a>
    </div>
    <div class="card-body p-0 mt-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4 py-3">Info Survei</th>
                        <th class="py-3 border-0">Visibilitas / ID</th>
                        <th class="py-3 border-0 text-center">Status</th>
                        <th class="py-3 border-0 text-center">Respons</th>
                        <th class="py-3 border-0 text-center pe-4">Aksi Builder & Analitik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surveys as $survey)
                    <tr class="border-bottom">
                        <td class="ps-4 py-4">
                            <h6 class="fw-bold mb-1 text-dark">{{ $survey->title }}</h6>
                            <div class="text-muted small lh-sm d-flex flex-wrap gap-2 mt-2">
                                <span class="badge bg-light text-dark border px-2 py-1"><i class="fa-solid fa-tag me-1 text-primary"></i> Kategori: <span class="text-capitalize">{{ str_replace('_', ' ', $survey->type) }}</span></span>
                                <span class="badge bg-light text-dark border px-2 py-1"><i class="fa-solid fa-list-ol me-1 text-info"></i> {{ $survey->questions_count }} Pertanyaan</span>
                            </div>
                        </td>
                        <td class="py-4">
                            @if($survey->is_anonymous)
                                <div class="text-secondary small fw-bold mb-1"><i class="fa-solid fa-user-secret me-1"></i> Anonim (Tanpa ID)</div>
                            @else
                                <div class="text-primary small fw-bold mb-1"><i class="fa-solid fa-address-card me-1"></i> Nama + Aktor Wajib</div>
                            @endif
                        </td>
                        <td class="py-4 text-center">
                            @if($survey->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-satellite-dish me-1"></i> Aktif (Live)</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-power-off me-1"></i> Tidak Aktif</span>
                            @endif
                        </td>
                        <td class="py-4 text-center">
                            <div class="d-inline-flex align-items-center bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1 fw-bold shadow-sm">
                                <i class="fa-solid fa-users me-2"></i> {{ $survey->responses_count }}
                            </div>
                        </td>
                        <td class="py-4 text-center pe-4">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.surveys.builder', $survey->id) }}" class="btn btn-outline-primary btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Desain Form & Pertanyaan">
                                    <i class="fa-solid fa-list-check me-1"></i> Builder
                                </a>
                                <a href="{{ route('admin.surveys.analytics', $survey->id) }}" class="btn btn-outline-success btn-sm rounded px-3 py-1 fw-bold shadow-sm" title="Lihat Hasil Analisis & Grafik">
                                    <i class="fa-solid fa-chart-pie me-1"></i> Analytics
                                </a>
                                
                                <form action="{{ route('admin.surveys.destroy', $survey->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus survei ini dan SELURUH data respons/jawabannya secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm rounded shadow-sm text-danger" title="Hapus Survei">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fa-solid fa-square-poll-vertical fs-1 mb-3 opacity-25"></i>
                                <p class="mb-0">Belum ada survei yang dibuat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($surveys->hasPages())
    <div class="card-footer bg-white pt-3 border-top-0 px-4">
        {{ $surveys->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
