@extends('layouts.admin')

@section('title', 'Buat Jadwal RTM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-landmark me-2 text-primary"></i>Buat Jadwal Rapat Tinjauan Manajemen</h4>
        <p class="text-muted mb-0 small">SPMI mengatur jadwal, mengundang peserta, dan menyiapkan agenda rapat berbasis hasil AMI.</p>
    </div>
    <a href="{{ route('admin.rtm.index') }}" class="btn btn-outline-secondary rounded-pill"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
</div>

<div class="card card-custom shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.rtm.store') }}" method="POST" class="row g-4">
            @csrf

            <div class="col-md-6">
                <label class="form-label fw-bold small">Judul RTM <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="cth: RTM Siklus 2025/2026 Ganjil" required>
                @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small">Siklus AMI <span class="text-danger">*</span></label>
                <select name="audit_cycle_id" class="form-select" required>
                    <option value="" disabled selected>Pilih siklus yang direview...</option>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ old('audit_cycle_id') == $cycle->id ? 'selected' : '' }}>
                            {{ $cycle->name }} ({{ $cycle->academic_year }} Sem. {{ $cycle->semester }}) — {{ strtoupper($cycle->status) }}
                        </option>
                    @endforeach
                </select>
                @error('audit_cycle_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Tanggal Rapat</label>
                <input type="date" name="meeting_date" class="form-control" value="{{ old('meeting_date') }}">
                @error('meeting_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Jam Mulai</label>
                <input type="time" name="meeting_time" class="form-control" value="{{ old('meeting_time') }}">
                @error('meeting_time')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Lokasi</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="cth: Ruang Rapat LPM">
                @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-bold small">Agenda Rapat</label>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <button type="button" id="btn-tarik-temuan" class="btn btn-outline-primary btn-sm rounded-pill fw-bold" disabled>
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Tarik Temuan AMI
                    </button>
                    <span class="text-muted small" id="tarik-info">Pilih Siklus AMI terlebih dahulu, lalu klik tombol ini. Sistem akan menarik temuan audit (diprioritaskan KTS) ke Agenda Rapat.</span>
                </div>
                <textarea name="agenda" id="agenda" class="form-control" rows="6" placeholder="Pokok-pokok yang akan dibahas...">{{ old('agenda') }}</textarea>
                @error('agenda')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-bold small">
                    <i class="fa-solid fa-user-plus me-1 text-primary"></i> Undang Peserta Rapat
                    <span class="text-muted fw-normal small">(Pimpinan, Kaprodi, Kepala Unit — di dalam sistem)</span>
                </label>
                <div class="row g-3">
                    @php
                        $groups = $invitees->groupBy(fn($u) => $u->roles->pluck('name')->first() ?? 'lainnya');
                    @endphp
                    @foreach($groups as $role => $users)
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold text-uppercase small text-primary mb-2">{{ $role }}</div>
                                @foreach($users as $user)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="participant_ids[]" value="{{ $user->id }}"
                                        id="p-{{ $user->id }}"
                                        {{ in_array($user->id, old('participant_ids', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="p-{{ $user->id }}">
                                        {{ $user->name }}
                                        @if($user->academicProgram) <span class="text-muted">({{ trim($user->academicProgram->degree_level . ' ' . $user->academicProgram->name) }})</span>
                                        @elseif($user->unit) <span class="text-muted">({{ $user->unit->name }})</span> @endif
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('participant_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.rtm.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-calendar-check me-2"></i> Simpan Jadwal RTM
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const cycleSelect = document.querySelector('select[name="audit_cycle_id"]');
    const btnTarik = document.getElementById('btn-tarik-temuan');
    const agenda = document.getElementById('agenda');
    const info = document.getElementById('tarik-info');

    function updateTarikState() {
        btnTarik.disabled = !cycleSelect.value;
    }
    cycleSelect.addEventListener('change', updateTarikState);
    updateTarikState();

    btnTarik.addEventListener('click', function () {
        if (!cycleSelect.value) return;
        const url = "{{ route('admin.rtm.findings', 0) }}".replace(/0$/, cycleSelect.value);
        btnTarik.disabled = true;
        btnTarik.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menarik temuan...';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                agenda.value = data.agenda;
                info.textContent = 'Berhasil menarik ' + data.count + ' temuan (' + data.kts + ' KTS). Silakan tinjau & sesuaikan sebelum menyimpan.';
                info.classList.remove('text-muted');
                info.classList.add('text-success');
            })
            .catch(() => {
                info.textContent = 'Gagal menarik temuan. Pastikan siklus dipilih dan coba lagi.';
                info.classList.remove('text-muted');
                info.classList.add('text-danger');
            })
            .finally(() => {
                btnTarik.disabled = false;
                btnTarik.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> Tarik Temuan AMI';
            });
    });
</script>
@endpush