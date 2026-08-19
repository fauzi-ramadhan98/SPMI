@extends('layouts.admin')

@section('title', 'Edit Jadwal RTM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fa-solid fa-landmark me-2 text-primary"></i>Edit Jadwal RTM</h4>
        <p class="text-muted mb-0 small">{{ $rtm->title }} — perbarui jadwal dan daftar peserta.</p>
    </div>
    <a href="{{ route('admin.rtm.show', $rtm->id) }}" class="btn btn-outline-secondary rounded-pill"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
</div>

<div class="card card-custom shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.rtm.update', $rtm->id) }}" method="POST" class="row g-4">
            @csrf @method('PUT')

            <div class="col-md-6">
                <label class="form-label fw-bold small">Judul RTM <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $rtm->title) }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small">Siklus AMI <span class="text-danger">*</span></label>
                <select name="audit_cycle_id" class="form-select" required>
                    @foreach($cycles as $cycle)
                        <option value="{{ $cycle->id }}" {{ old('audit_cycle_id', $rtm->audit_cycle_id) == $cycle->id ? 'selected' : '' }}>
                            {{ $cycle->name }} ({{ $cycle->academic_year }} Sem. {{ $cycle->semester }}) — {{ strtoupper($cycle->status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Tanggal Rapat</label>
                <input type="date" name="meeting_date" class="form-control" value="{{ old('meeting_date', $rtm->meeting_date?->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Jam Mulai</label>
                <input type="time" name="meeting_time" class="form-control" value="{{ old('meeting_time', $rtm->meeting_time?->format('H:i')) }}">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold small">Lokasi</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $rtm->location) }}">
            </div>

            <div class="col-12">
                <label class="form-label fw-bold small">Agenda Rapat</label>
                <textarea name="agenda" class="form-control" rows="3">{{ old('agenda', $rtm->agenda) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold small">
                    <i class="fa-solid fa-user-plus me-1 text-primary"></i> Undang Peserta Rapat
                </label>
                <div class="row g-3">
                    @php
                        $selected = old('participant_ids', $rtm->participants->pluck('id')->all());
                        $groups = $invitees->groupBy(fn($u) => $u->roles->pluck('name')->first() ?? 'lainnya');
                    @endphp
                    @foreach($groups as $role => $users)
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="fw-bold text-uppercase small text-primary mb-2">{{ $role }}</div>
                                @foreach($users as $user)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" name="participant_ids[]" value="{{ $user->id }}"
                                        id="p-{{ $user->id }}" {{ in_array($user->id, $selected) ? 'checked' : '' }}>
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
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.rtm.show', $rtm->id) }}" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection