@extends('layouts.admin')

@section('title', 'Evaluasi Diri — ' . $evaluation->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 mb-1">{{ $evaluation->name }}</h1>
        <p class="text-muted mb-0">
            {{ $evaluation->owner_type_label }} {{ $evaluation->owner_label }} — {{ $evaluation->academic_year }} {{ $evaluation->semester }}
            @if($evaluation->status === 'verified')
                <span class="badge bg-success">Terverifikasi · Siap Audit</span>
            @elseif($evaluation->status === 'submitted')
                <span class="badge bg-warning text-dark">Submitted — Menunggu Verifikasi</span>
            @else
                <span class="badge bg-secondary">Draft</span>
            @endif
            @if($evaluation->conclusion)
                <span class="d-block mt-1 small" style="white-space: pre-wrap;"><i class="fa-solid fa-stamp text-success me-1"></i>Kesimpulan verifikasi: {{ $evaluation->conclusion }}</span>
            @endif
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if ($canFill && $evaluation->status !== 'verified')
            <form action="{{ route('admin.evaluations.submit', $evaluation) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success" onclick="return confirm('Kunci & serahkan ED untuk peninjauan?')">
                    <i class="fa-solid fa-paper-plane"></i> Lengkapi &amp; Serahkan
                </button>
            </form>
        @endif
        @if ($canVerify && $evaluation->status === 'submitted')
            <form action="{{ route('admin.evaluations.verify', $evaluation) }}" method="POST" class="d-inline">
                @csrf
                <input type="text" name="conclusion" class="form-control form-control-sm d-inline-block" style="width:220px" placeholder="Kesimpulan verifikasi">
                <button class="btn btn-primary">Verifikasi</button>
            </form>
        @endif
    </div>
</div>

{{-- Bukti umum ED --}}
<div class="card mb-4">
    <div class="card-header">Bukti / Lampiran Umum Evaluasi</div>
    <div class="card-body">
        @forelse ($evaluation->attachments as $a)
            <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                <div>
                    <strong>{{ $a->title ?: 'Lampiran' }}</strong>
                    @if ($a->category)<span class="badge bg-light text-dark">{{ $a->category }}</span>@endif
                    <div class="small">
                        @if ($a->file_path)
                            <a href="{{ asset('storage/' . $a->file_path) }}" target="_blank"><i class="fa-solid fa-download"></i> {{ $a->file_name }}</a>
                        @elseif ($a->link)
                            <a href="{{ $a->link }}" target="_blank"><i class="fa-solid fa-link"></i> {{ $a->link }}</a>
                        @else — @endif
                    </div>
                </div>
                @if ($canFill)
                <form method="POST" action="{{ route('admin.evaluations.attachments.destroy', [$evaluation, $a]) }}" onsubmit="return confirm('Hapus bukti?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                </form>
                @endif
            </div>
        @empty
            <p class="text-muted mb-0 small">Belum ada bukti umum.</p>
        @endforelse
    </div>
    @if ($canFill && $evaluation->status !== 'verified')
    <div class="card-footer">
        <form method="POST" action="{{ route('admin.evaluations.attachments.store', $evaluation) }}" enctype="multipart/form-data" class="row g-2">
            @csrf
            <div class="col-md-3"><input class="form-control form-control-sm" name="title" placeholder="cth: Notulen Rapat Tinjauan Kurikulum"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="category" placeholder="cth: Notulen / SK / Daftar Hadir / Laporan"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" type="file" name="file"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="link" placeholder="cth: https://drive.google.com/file/d/xxxx"></div>
            <div class="col-md-2 text-end"><button class="btn btn-sm btn-primary w-100">Unggah</button></div>
            <div class="col-12">
                <small class="text-muted"><i class="fa-solid fa-circle-info me-1"></i>Isi salah satu: File ATAU Link Drive. Contoh: Judul "SK Penetapan Standar Mutu", Kategori "SK", File PDF / link Drive.</small>
            </div>
        </form>
    </div>
    @endif
</div>

{{-- Isi evaluasi (per indikator) --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Penilaian per Indikator</span>
        @if ($canFill && $evaluation->status !== 'verified')
        <form action="{{ route('admin.evaluations.generate-from-checklist', $evaluation) }}" method="POST" class="d-flex flex-wrap align-items-center gap-2">
            @csrf
            <label class="small fw-bold mb-0">Filter berdasarkan Standar:</label>
            <select name="standard_id" class="form-select form-select-sm" style="max-width:200px">
                <option value="">Semua Standar</option>
                @foreach ($standards as $s)
                    <option value="{{ $s->id }}">{{ $s->kode_standar }} — {{ $s->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-success" title="Generate indikator dari Daftar Tilik SPMI">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Generate dari Daftar Tilik
            </button>
        </form>
        @endif
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('admin.evaluations.items.update', $evaluation) }}">
            @csrf @method('PUT')
            @forelse ($evaluation->items as $item)
                @php $it = $item->id; @endphp
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <strong>
                                @if ($item->standard)<span class="badge bg-info text-dark me-1">{{ $item->standard->kode_standar }}</span>@endif
                                {{ $item->indicator }}
                            </strong>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if ($canFill && $evaluation->status !== 'verified')
                            <div>
                                <label class="small mb-0 fw-bold">Penilaian Mandiri <span class="text-danger">*</span></label>
                                <select name="items[{{ $it }}][self_assessment]" class="form-select form-select-sm {{ $errors->first('items.'.$it.'.self_assessment') ? 'is-invalid' : '' }}">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="Tercapai" {{ old('items.'.$it.'.self_assessment', $item->self_assessment) == 'Tercapai' ? 'selected' : '' }}>Tercapai</option>
                                    <option value="Belum Tercapai" {{ old('items.'.$it.'.self_assessment', $item->self_assessment) == 'Belum Tercapai' ? 'selected' : '' }}>Belum Tercapai</option>
                                </select>
                                @if($errors->first('items.'.$it.'.self_assessment'))<div class="invalid-feedback">{{ $errors->first('items.'.$it.'.self_assessment') }}</div>@endif
                            </div>
                            @else
                                <span class="badge {{ $item->self_assessment == 'Tercapai' ? 'bg-success' : 'bg-danger' }}">{{ $item->self_assessment ?? '—' }}</span>
                            @endif
                            <label class="small mb-0 fw-bold">Skor</label>
                            @if ($canFill && $evaluation->status !== 'verified')
                                <input type="number" min="0" max="4" step="0.5" name="items[{{ $it }}][score]" class="form-control form-control-sm" style="width:70px" value="{{ old('items.'.$it.'.score', $item->score ?? '') }}">
                            @else
                                <span class="small fw-bold text-dark">
                                    <i class="fa-solid fa-gauge-high me-1 text-muted"></i>
                                    Skor Penilaian Mandiri (Prodi):
                                    <span class="badge bg-secondary fs-6 ms-1">{{ $item->score ?? '—' }}</span>
                                </span>
                            @endif
                            @if ($canFill && $evaluation->status !== 'verified')
                            <button type="submit" form="delete-item-{{ $item->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus indikator + buktinya?')"><i class="fa-solid fa-trash"></i></button>
                            @endif
                        </div>
                    </div>

                    @php
                        $itemRisks = $ownerRisks->filter(function ($risk) use ($item) {
                            $stdName = trim($item->standard?->name ?? '');
                            if ($stdName && strcasecmp(trim((string)$risk->standar_mutu), $stdName) === 0) {
                                return true;
                            }
                            $indicator = trim((string)$item->indicator);
                            $butir = trim((string)$risk->butir_tilik);
                            if ($indicator && $butir && (str_contains($indicator, $butir) || str_contains($butir, $indicator))) {
                                return true;
                            }
                            return false;
                        })->values();
                    @endphp

                    {{-- Informasi Profil Risiko Awal (statis dari Risk Register) --}}
                    @if($itemRisks->isNotEmpty())
                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 py-2 px-3 mb-3 small">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-circle-info me-1"></i>Informasi Profil Risiko Awal</div>
                        @foreach($itemRisks as $risk)
                            <div class="mb-1">Potensi risiko adalah <em>"{{ $risk->risk_description }}"</em> dengan rencana mitigasi <em>"{{ $risk->mitigation_plan }}"</em>.</div>
                        @endforeach
                    </div>
                    @endif

                    <div class="row g-2">
                        <div class="col-12">
                            <label class="small text-muted fw-bold">Deskripsi Capaian &amp; Analisis @if ($canFill && $evaluation->status !== 'verified')<span class="text-danger">*</span>@endif</label>
                            @if ($canFill && $evaluation->status !== 'verified')
                            <div class="form-text mb-1 small">Ceritakan realita saat ini dan analisis apakah mitigasi awal di atas berhasil atau gagal (wajib diisi).</div>
                            <textarea name="items[{{ $it }}][narasi]" rows="3" class="form-control form-control-sm {{ $errors->first('items.'.$it.'.narasi') ? 'is-invalid' : '' }}" required>{{ old('items.'.$it.'.narasi', $item->narasi) }}</textarea>
                            @if($errors->first('items.'.$it.'.narasi'))<div class="invalid-feedback">{{ $errors->first('items.'.$it.'.narasi') }}</div>@endif
                            @else
                            <div class="border rounded-2 p-2 small bg-white" style="white-space: pre-wrap;">{{ $item->narasi ?? '—' }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Bukti per indikator --}}
                    <div class="border rounded-2 p-2 mt-3 bg-light">
                        <div class="small fw-bold mb-2"><i class="fa-solid fa-paperclip me-1"></i> Bukti Indikator (Notulen, Daftar Hadir, Dokumentasi/Foto)</div>
                        @forelse ($item->attachments as $b)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-1 small">
                                <div>
                                    @if ($b->file_path)<a href="{{ asset('storage/' . $b->file_path) }}" target="_blank"><i class="fa-solid fa-file me-1 text-primary"></i>{{ $b->file_name }}</a>
                                    @elseif ($b->link)<a href="{{ $b->link }}" target="_blank"><i class="fa-solid fa-link me-1 text-primary"></i>{{ $b->link }}</a>
                                    @endif
                                    @if ($b->title)<span class="text-muted ms-2">— {{ $b->title }}</span>@endif
                                </div>
                                @if ($canFill && $evaluation->status !== 'verified')
                                <button type="submit" form="delete-att-{{ $b->id }}" class="btn btn-link btn-sm text-danger p-0">&times;</button>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted small">Belum ada bukti.</div>
                        @endforelse

                        @if ($canFill && $evaluation->status !== 'verified')
                        <form method="POST" action="{{ route('admin.evaluations.attachments.store', $evaluation) }}" enctype="multipart/form-data" class="row g-2 align-items-end mt-2 border-top pt-2">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <div class="col-md-5">
                                <label class="small text-muted">File Bukti</label>
                                <input class="form-control form-control-sm" type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            </div>
                            <div class="col-md-5">
                                <label class="small text-muted">Atau Link Google Drive</label>
                                <input class="form-control form-control-sm" name="link" placeholder="https://drive.google.com/...">
                            </div>
                            <div class="col-md-2 text-end">
                                <button class="btn btn-sm btn-success w-100"><i class="fa-solid fa-upload"></i> Unggah</button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    Belum ada indikator. @if ($canFill && $evaluation->status !== 'verified') Gunakan tombol "Generate dari Daftar Tilik" untuk membuat indikator sesuai instrumen SPMI.@endif
                </div>
            @endforelse

            @if ($canFill && $evaluation->status !== 'verified' && $evaluation->items->isNotEmpty())
            <div class="p-3">
                <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Evaluasi Diri</button>
            </div>
            @endif
        </form>

        @if ($canFill && $evaluation->status !== 'verified')
        <div class="p-3 border-top bg-white">
            <p class="text-muted small mb-0"><i class="fa-solid fa-circle-info me-1"></i> Unggah bukti dilakukan per indikator melalui tombol "Unggah" pada masing-masing indikator di atas. Bukti umum (tanpa indikator) dilampirkan pada kartu "Bukti / Lampiran Umum Evaluasi".</p>
        </div>

        @foreach ($evaluation->items as $item)
            <form id="delete-item-{{ $item->id }}" method="POST" action="{{ route('admin.evaluations.items.destroy', [$evaluation, $item]) }}" class="d-none">@csrf @method('DELETE')</form>
            @foreach ($item->attachments as $b)
                <form id="delete-att-{{ $b->id }}" method="POST" action="{{ route('admin.evaluations.attachments.destroy', [$evaluation, $b]) }}" class="d-none">@csrf @method('DELETE')</form>
            @endforeach
        @endforeach
        @endif
    </div>
</div>
@endsection