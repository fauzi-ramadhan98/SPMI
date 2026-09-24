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
                    <span class="text-muted small" id="tarik-info">Pilih Siklus AMI terlebih dahulu, lalu klik tombol ini. Sistem akan menarik temuan audit (diprioritaskan KTS) untuk disusun ke Agenda Rapat.</span>
                </div>

                {{-- Checklist Agenda Rapat --}}
                <div id="agenda-checklist" class="border rounded-4 p-3 bg-white" style="max-height: 400px; overflow-y: auto; display: none;">
                    <div class="mb-2">
                        <button type="button" id="btn-pilih-semua" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fa-solid fa-check-double me-1"></i> Pilih Semua
                        </button>
                        <button type="button" id="btn-batal-pilih" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-1">
                            <i class="fa-solid fa-xmark me-1"></i> Batal Pilih
                        </button>
                        <span class="text-muted small ms-auto" id="pilih-count">0 item dipilih</span>
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 small" id="agenda-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="check-all" class="form-check-input">
                                    </th>
                                    <th style="width: 100px;">Tipe</th>
                                    <th style="min-width: 150px;">Auditee</th>
                                    <th style="min-width: 200px;">Standar / Kriteria</th>
                                    <th style="min-width: 250px;">Deskripsi Temuan</th>
                                    <th style="width: 100px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="agenda-tbody">
                                <!-- diisi via JS -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2 d-none" id="agenda-empty">
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-magnifying-glass fa-2x mb-2"></i>
                            <div>Belum ada temuan untuk siklus ini</div>
                        </div>
                    </div>
                </div>

                {{-- Hidden input untuk menyimpan ID temuan yang dipilih --}}
                <input type="hidden" name="selected_findings" id="selected-findings" value="">
                <textarea name="agenda" id="agenda" class="form-control d-none" rows="6" placeholder="Pokok-pokok yang akan dibahas...">{{ old('agenda') }}</textarea>
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
    const checklistDiv = document.getElementById('agenda-checklist');
    const tbody = document.getElementById('agenda-tbody');
    const checklistEmpty = document.getElementById('agenda-empty');
    const selectAllBtn = document.getElementById('btn-pilih-semua');
    const btnClear = document.getElementById('btn-batal-pilih');
    const countEl = document.getElementById('pilih-count');
    const checkAll = document.getElementById('check-all');
    const hiddenInput = document.getElementById('selected-findings');
    const agendaHidden = document.getElementById('agenda');

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
                renderChecklist(data);
                info.textContent = 'Berhasil menarik ' + data.total + ' temuan (' + data.kts + ' KTS). Centang temuan yang ingin diangkat ke Agenda Rapat, lalu Simpan.';
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

    function renderChecklist(data) {
        const tbody = document.getElementById('agenda-tbody');
        const checklistDiv = document.getElementById('agenda-checklist');
        const emptyDiv = document.getElementById('agenda-empty');
        tbody.innerHTML = '';

        if (!data.findings || data.findings.length === 0) {
            checklistDiv.style.display = 'none';
            document.getElementById('agenda-empty').style.display = 'block';
            return;
        }

        checklistDiv.style.display = 'block';
        document.getElementById('agenda-empty').style.display = 'none';

        data.findings.forEach(group => {
            // Group header
            const groupRow = document.createElement('tr');
            groupRow.className = 'table-active fw-bold';
            groupRow.innerHTML = `
                <td colspan="6">
                    <i class="fa-solid fa-building me-1"></i> ${group.auditee}
                    <span class="badge bg-secondary ms-2 small">${group.items.length} temuan</span>
                </td>
            `;
            tbody.appendChild(groupRow);

            group.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.dataset.id = item.id;
                const typeBadge = item.type === 'KTS' 
                    ? '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 small">KTS</span>'
                    : '<span class="badge bg-info bg-opacity-10 text-info border">OB</span>';
                const statusBadge = item.status === 'closed' 
                    ? '<span class="badge bg-success bg-opacity-10 text-success">Ditindaklanjuti</span>'
                    : (item.status === 'in_progress' 
                        ? '<span class="badge bg-warning text-dark">Dalam proses</span>'
                        : '<span class="badge bg-secondary">Belum</span>');

                tr.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input finding-check" value="${item.id}" data-id="${item.id}">
                    </td>
                    <td class="text-center">${typeBadge}</td>
                    <td><span class="badge bg-light text-dark border">${item.auditee_label}</span></td>
                    <td><strong>${item.criteria}</strong></td>
                    <td>
                        <div class="small">${item.description ? item.description.substring(0, 150) + (item.description.length > 150 ? '...' : '') : '(Kosong)'}</div>
                        ${item.corrective_action ? '<div class="text-muted small mt-1"><i class="fa-solid fa-arrow-right me-1"></i> RTL: ' + item.corrective_action.substring(0, 100) + (item.corrective_action.length > 100 ? '...' : '') + '</div>' : ''}
                    </td>
                    <td class="text-center">${item.status_label}</td>
                `;
                tbody.appendChild(tr);
            });
        });

        updatePilihCount();
    }

    function updatePilihCount() {
        const checked = document.querySelectorAll('.finding-check:checked').length;
        countEl.textContent = checked + ' item dipilih';
        // Update hidden input
        const selected = Array.from(document.querySelectorAll('.finding-check:checked')).map(c => c.value);
        document.getElementById('selected-findings').value = selected.join(',');
    }

    // Check all / uncheck all
    document.getElementById('check-all').addEventListener('change', function(e) {
        document.querySelectorAll('.finding-check').forEach(cb => cb.checked = e.target.checked);
        updatePilihCount();
    });

    document.getElementById('agenda-tbody').addEventListener('change', function(e) {
        if (e.target.classList.contains('finding-check')) {
            updatePilihCount();
        }
    });

    document.getElementById('btn-pilih-semua').addEventListener('click', function() {
        document.querySelectorAll('.finding-check').forEach(cb => cb.checked = true);
        updatePilihCount();
    });

    document.getElementById('btn-batal-pilih').addEventListener('click', function() {
        document.querySelectorAll('.finding-check').forEach(cb => cb.checked = false);
        updatePilihCount();
    });

    // Saat form disubmit, generate agenda text dari item yang dipilih
    document.querySelector('form').addEventListener('submit', function(e) {
        const selected = Array.from(document.querySelectorAll('.finding-check:checked')).map(c => c.value);
        if (selected.length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu temuan untuk dijadikan Agenda Rapat.');
            return false;
        }
        // Generate agenda text for hidden field
        const rows = document.querySelectorAll('#agenda-tbody tr[data-id]');
        let agendaText = 'Agenda Rapat Tinjauan Manajemen\n(Temuan dipilih oleh SPMI untuk dibahas di RTM)\n\n';
        let currentAuditee = '';
        document.querySelectorAll('#agenda-tbody tr[data-id]').forEach(tr => {
            if (tr.classList.contains('table-active')) {
                currentAuditee = tr.cells[0].textContent.trim();
            } else if (tr.querySelector('.finding-check')?.checked) {
                const auditee = tr.cells[2].textContent.trim();
                const criteria = tr.cells[3].textContent.trim();
                const typeBadge = tr.cells[1].textContent.trim();
                agendaText += '[' + (auditee !== currentAuditee ? (currentAuditee = auditee, auditee) : '') + ']\n';
                agendaText += (typeBadge.includes('KTS') ? '[KTS] ' : '[OB] ') + criteria + '\n';
            });
        });
        document.getElementById('agenda').value = agendaText.trim();
    });

    // Pastikan selected_findings di-submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const selected = Array.from(document.querySelectorAll('.finding-check:checked')).map(c => c.value);
        document.getElementById('selected-findings').value = selected.join(',');
    });

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
                renderChecklist(data);
                info.textContent = 'Berhasil menarik ' + data.total + ' temuan (' + data.kts + ' KTS). Centang temuan yang ingin diangkat ke Agenda Rapat, lalu Simpan.';
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