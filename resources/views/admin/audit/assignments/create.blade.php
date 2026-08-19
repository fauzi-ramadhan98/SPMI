@extends('layouts.admin')

@section('title', 'Tambah Alokasi Auditor')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-sm btn-light rounded-circle me-3 border"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h5 class="fw-bold mb-0">Tambah Alokasi Auditor Baru</h5>
                        <p class="text-muted small mb-0">Petakan auditor ke program studi untuk siklus AMI yang sedang aktif.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4 p-md-5 pt-3">
                <form action="{{ route('admin.audit.assignments.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Siklus AMI <span class="text-danger">*</span></label>
                        <select name="audit_cycle_id" class="form-select form-select-lg bg-light border-0 @error('audit_cycle_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih Siklus AMI...</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" {{ old('audit_cycle_id') == $cycle->id ? 'selected' : '' }}>
                                    {{ $cycle->name }} ({{ $cycle->academic_year }} - Sem. {{ $cycle->semester }})
                                    [{{ strtoupper($cycle->status) }}]
                                </option>
                            @endforeach
                        </select>
                        @error('audit_cycle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($cycles->isEmpty())
                            <div class="form-text text-danger fw-semibold mt-2">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> Belum ada siklus AMI yang tersedia untuk dialokasikan.
                                <a href="{{ route('admin.audit.cycles.create') }}">Buat siklus terlebih dahulu.</a>
                            </div>
                        @else
                            <div class="form-text text-muted mt-1">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Hanya siklus berstatus [DRAFT] / [AKTIF] yang dapat dialokasikan. Siklus [SELESAI] disembunyikan.
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Jenis Auditee <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="auditee_type" id="auditee_prodi" value="prodi" checked>
                                <label class="form-check-label" for="auditee_prodi"><i class="fa-solid fa-building-columns me-1"></i> Program Studi</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="auditee_type" id="auditee_unit" value="unit">
                                <label class="form-check-label" for="auditee_unit"><i class="fa-solid fa-building me-1"></i> Unit Kerja</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4" id="program-select-group">
                        <label class="form-label fw-bold text-muted small">Program Studi (Auditee) <span class="text-danger">*</span></label>
                        <select name="academic_program_id" class="form-select form-select-lg bg-light border-0 @error('academic_program_id') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih Program Studi yang Diaudit...</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('academic_program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->degree_level }} {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_program_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4" id="unit-select-group" style="display: none;">
                        <label class="form-label fw-bold text-muted small">Unit Kerja (Auditee) <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select form-select-lg bg-light border-0 @error('unit_id') is-invalid @enderror">
                            <option value="" disabled selected>Pilih Unit Kerja yang Diaudit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->category_label }})
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Peran dalam Tim Audit <span class="text-danger">*</span></label>
                        <select name="auditor_role" class="form-select form-select-lg bg-light border-0 @error('auditor_role') is-invalid @enderror" required>
                            <option value="" disabled {{ old('auditor_role') ? '' : 'selected' }}>Pilih Peran...</option>
                            <option value="Ketua" {{ old('auditor_role') == 'Ketua' ? 'selected' : '' }}>Ketua Tim Audit</option>
                            <option value="Anggota" {{ old('auditor_role') == 'Anggota' ? 'selected' : '' }}>Anggota Tim Audit</option>
                        </select>
                        @error('auditor_role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text text-muted mt-1"><i class="fa-solid fa-circle-info me-1"></i>Audit dilakukan secara berkelompok. Tandai satu auditor sebagai <strong>Ketua</strong> dan lainnya sebagai <strong>Anggota</strong>.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Kategori Auditor <span class="text-danger">*</span></label>
                        <select name="auditor_type" class="form-select form-select-lg bg-light border-0 @error('auditor_type') is-invalid @enderror" required>
                            <option value="" disabled selected>Pilih Kategori Auditor...</option>
                            <option value="Auditor Internal" {{ old('auditor_type') == 'Auditor Internal' ? 'selected' : '' }}>Auditor Internal</option>
                            <option value="Auditor External" {{ old('auditor_type') == 'Auditor External' ? 'selected' : '' }}>Auditor External</option>
                        </select>
                        @error('auditor_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Pilih Auditor (Akun Terdaftar) <span class="text-danger">*</span></label>
                        <select name="auditor_id" id="auditor_id" class="form-select form-select-lg bg-light border-0 @error('auditor_id') is-invalid @enderror" required onchange="fillAuditorIdentity(this)">
                            <option value="" disabled selected>Pilih Auditor...</option>
                            @foreach($auditorUsers as $auditorUser)
                                <option value="{{ $auditorUser->id }}"
                                    data-name="{{ $auditorUser->name }}"
                                    data-nidn="{{ $auditorUser->nidn }}"
                                    data-homebase="{{ $auditorUser->academic_program_id }}"
                                    {{ old('auditor_id') == $auditorUser->id ? 'selected' : '' }}>
                                    {{ $auditorUser->name }} — {{ $auditorUser->email }}
                                    @if($auditorUser->academicProgram) ({{ $auditorUser->academicProgram->degree_level }} {{ $auditorUser->academicProgram->name }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('auditor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($auditorUsers->isEmpty())
                            <div class="form-text text-danger fw-semibold mt-2">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> Belum ada akun dengan role <strong>auditor</strong>.
                                <a href="{{ route('admin.users.index') }}">Tambah user auditor terlebih dahulu.</a>
                            </div>
                        @endif
                    </div>

                    {{-- Hidden: auditor_name & auditor_nidn diisi otomatis dari pilihan dropdown --}}
                    <input type="hidden" name="auditor_name" id="auditor_name" value="{{ old('auditor_name') }}">

                    <div class="mb-5">
                        <label class="form-label fw-bold text-muted small">NIDN/NIK</label>
                        <div class="input-group">
                            <input type="text" name="auditor_nidn" id="auditor_nidn" class="form-control form-control-lg bg-light @error('auditor_nidn') is-invalid @enderror" value="{{ old('auditor_nidn') }}" readonly>
                            <span class="input-group-text bg-white border-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        </div>
                        <div class="form-text"><i class="fa-solid fa-circle-info me-1"></i>Diisi otomatis dari profil akun auditor. Hubungi admin bila NIDN belum terdaftar.</div>
                        @error('auditor_nidn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.audit.assignments.index') }}" class="btn btn-light border rounded-pill px-4 fw-semibold">Batal</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fa-solid fa-user-check me-2"></i> Simpan Alokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fillAuditorIdentity(select) {
    const selected = select.options[select.selectedIndex];
    document.getElementById('auditor_name').value = selected.dataset.name || '';
    document.getElementById('auditor_nidn').value = selected.dataset.nidn || '';
}
// Toggle auditee prodi/unit
document.addEventListener('DOMContentLoaded', function () {
    const prodiGroup = document.getElementById('program-select-group');
    const unitGroup = document.getElementById('unit-select-group');
    const prodiRadio = document.getElementById('auditee_prodi');
    const unitRadio = document.getElementById('auditee_unit');
    const prodiSelect = prodiGroup.querySelector('select');
    const unitSelect = unitGroup.querySelector('select');

    function toggleAuditee() {
        if (unitRadio.checked) {
            prodiGroup.style.display = 'none';
            unitGroup.style.display = 'block';
            prodiSelect.removeAttribute('required');
            prodiSelect.value = '';
            unitSelect.setAttribute('required', 'required');
        } else {
            prodiGroup.style.display = 'block';
            unitGroup.style.display = 'none';
            unitSelect.removeAttribute('required');
            unitSelect.value = '';
            prodiSelect.setAttribute('required', 'required');
        }
    }

    prodiRadio.addEventListener('change', toggleAuditee);
    unitRadio.addEventListener('change', toggleAuditee);

    // Restore state dari old()
    const oldType = "{{ old('unit_id') }}";
    if (oldType) {
        unitRadio.checked = true;
        toggleAuditee();
    }

    const select = document.getElementById('auditor_id');
    if (select && select.value) fillAuditorIdentity(select);
});
</script>
@endpush
