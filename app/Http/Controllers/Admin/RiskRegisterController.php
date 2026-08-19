<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiskRegister;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
use App\Models\AuditAssignment;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RiskRevisionRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class RiskRegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = RiskRegister::with(['academicProgram', 'unit', 'user', 'validatedBy'])->orderBy('created_at', 'desc');
        $user = auth()->user();

        if ($user->hasRole('prodi')) {
            $query->where('academic_program_id', $user->academic_program_id);
        } elseif ($user->hasRole('unit')) {
            $query->where('unit_id', $user->unit_id);
        } elseif ($user->hasRole('auditor')) {
            $assignedProdiIds = AuditAssignment::where('auditor_id', $user->id)
                ->whereNotNull('academic_program_id')
                ->pluck('academic_program_id');
            $assignedUnitIds = AuditAssignment::where('auditor_id', $user->id)
                ->whereNotNull('unit_id')
                ->pluck('unit_id');
            $query->where(function ($q) use ($assignedProdiIds, $assignedUnitIds) {
                $q->whereIn('academic_program_id', $assignedProdiIds)
                  ->orWhereIn('unit_id', $assignedUnitIds);
            });
        }

        // Filter toolbar (SPMI / pimpinan) — seluruh risiko semua prodi
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }
        if ($request->filled('academic_program_id')) {
            $query->where('academic_program_id', $request->academic_program_id);
        }
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $risks = $query->paginate(10)->withQueryString();

        $programs = AcademicProgram::where('is_active', true)->orderBy('name')->get();
        $yearOptions = RiskRegister::distinct()->orderByDesc('academic_year')->pluck('academic_year');
        $filters = $request->only(['academic_year', 'academic_program_id', 'risk_level']);

        return view('admin.risk_registers.index', compact('risks', 'programs', 'yearOptions', 'filters'));
    }

    public function create()
    {
        $this->authorizeWrite();

        $user = auth()->user();
        $lockedProgramId = null;
        $lockedUnitId = null;

        if ($user->hasRole('prodi')) {
            $programs = AcademicProgram::where('id', $user->academic_program_id)->get();
            $units = collect();
            $lockedProgramId = $user->academic_program_id;
        } elseif ($user->hasRole('unit')) {
            $programs = collect();
            $units = Unit::where('id', $user->unit_id)->get();
            $lockedUnitId = $user->unit_id;
        } else {
            $programs = AcademicProgram::all();
            $units = Unit::all();
        }

        $standards = \App\Models\QualityStandard::where('is_active', true)->get();
        $academicYears = AcademicYear::active();

        return view('admin.risk_registers.create', compact('programs', 'units', 'standards', 'academicYears', 'lockedProgramId', 'lockedUnitId'));
    }

    public function store(Request $request)
    {
        $this->authorizeWrite();

        $validated = $this->validateRiskInput($request);

        $validated['risk_score'] = $validated['probability'] * $validated['impact'];
        $validated['risk_level'] = $this->riskLevel($validated['risk_score']);
        $validated['created_by'] = auth()->id();

        RiskRegister::create($validated);
        return redirect()->route('admin.risk-registers.index')->with('success', 'Profil Risiko berhasil ditambahkan.');
    }

    public function show(RiskRegister $riskRegister)
    {
        return redirect()->route('admin.risk-registers.edit', $riskRegister);
    }

    public function edit(RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);

        $user = auth()->user();
        $lockedProgramId = null;
        $lockedUnitId = null;

        if ($user->hasRole('prodi')) {
            $programs = AcademicProgram::where('id', $user->academic_program_id)->get();
            $units = collect();
            $lockedProgramId = $user->academic_program_id;
        } elseif ($user->hasRole('unit')) {
            $programs = collect();
            $units = Unit::where('id', $user->unit_id)->get();
            $lockedUnitId = $user->unit_id;
        } else {
            $programs = AcademicProgram::all();
            $units = Unit::all();
        }

        $standards = \App\Models\QualityStandard::where('is_active', true)->get();
        $academicYears = AcademicYear::active();

        return view('admin.risk_registers.edit', compact('riskRegister', 'programs', 'units', 'standards', 'academicYears', 'lockedProgramId', 'lockedUnitId'));
    }

    public function update(Request $request, RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);

        $validated = $this->validateRiskInput($request);

        $validated['risk_score'] = $validated['probability'] * $validated['impact'];
        $validated['risk_level'] = $this->riskLevel($validated['risk_score']);

        $riskRegister->update($validated + ['status' => 'pending', 'status_note' => null]);
        return redirect()->route('admin.risk-registers.index')->with('success', 'Profil Risiko berhasil diperbarui.');
    }

    /**
     * SPMI: Validasi / menyetujui mitigasi Risk Owner.
     */
    public function validateRisk(Request $request, RiskRegister $riskRegister)
    {
        abort_unless(auth()->user()->hasRole('spmi'), 403, 'Hanya SPMI yang dapat memvalidasi profil risiko.');

        $riskRegister->update([
            'status' => 'approved',
            'status_note' => null,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Profil risiko disetujui (Validasi SPMI).');
    }

    /**
     * SPMI: Beri Catatan → Risk Owner wajib merevisi, dan mendapat notifikasi di dasbor.
     */
    public function note(Request $request, RiskRegister $riskRegister)
    {
        abort_unless(auth()->user()->hasRole('spmi'), 403, 'Hanya SPMI yang dapat memberi catatan revisi.');

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ], ['note.required' => 'Catatan revisi wajib diisi.']);

        $riskRegister->update([
            'status' => 'revision',
            'status_note' => $validated['note'],
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        $this->notifyOwnerRevision($riskRegister, $validated['note']);

        return back()->with('success', 'Catatan dikirim ke Risk Owner. Status risiko menjadi "Perlu Revisi".');
    }

    public function destroy(RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);
        $riskRegister->delete();
        return redirect()->route('admin.risk-registers.index')->with('success', 'Profil Risiko berhasil dihapus.');
    }

    public function updateDocumentLink(Request $request, RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);
        $request->validate(['document_link' => 'nullable|url']);
        $riskRegister->update(['document_link' => $request->document_link]);
        return back()->with('success', 'Link bukti dokumen berhasil disimpan.');
    }

    private function validateRiskInput(Request $request)
    {
        $yearNames = AcademicYear::active()->pluck('name')->all();

        $validated = $request->validate([
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'unit_id'             => 'nullable|exists:units,id',
            'academic_year'       => ['required', 'string', Rule::in($yearNames)],
            'standar_mutu'        => 'required|string|max:255',
            'butir_tilik'         => 'required|string',
            'risk_description'    => 'required|string',
            'temuan'              => 'required|string',
            'akar_masalah'        => 'required|string',
            'impact'              => 'required|integer|min:1|max:5',
            'probability'         => 'required|integer|min:1|max:5',
            'mitigation_plan'     => 'required|string',
            'document_link'       => 'nullable|url',
            'pic'                 => 'required|string|max:255',
            'target_date'         => 'required|date',
        ], [
            'academic_year.required'     => 'Tahun Akademik wajib dipilih.',
            'academic_year.in'           => 'Tahun Akademik yang dipilih tidak valid. Pilih dari daftar Master Tahun Akademik.',
            'akar_masalah.required'      => 'Akar Masalah wajib diisi.',
            'mitigation_plan.required' => 'Mitigasi wajib diisi. Jika ada risiko, harus ada rencana penanganannya.',
            'pic.required'             => 'PIC (Penanggungjawab) wajib diisi.',
            'target_date.required'     => 'Tanggal Penyelesaian wajib diisi.',
        ]);

        // Wajib memilih salah satu pemilik: prodi ATAU unit
        if (!$request->filled('academic_program_id') && !$request->filled('unit_id')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'academic_program_id' => 'Pilih salah satu pemilik risiko: Program Studi atau Unit Kerja.',
            ]);
        }

        return $validated;
    }

    private function riskLevel(int $score): string
    {
        if ($score >= 15) {
            return 'High';
        } elseif ($score >= 8) {
            return 'Medium';
        }
        return 'Low';
    }

    /**
     * Prodi/Unit hanya dapat mengelola profil risiko miliknya sendiri.
     */
    private function authorizeAccess(RiskRegister $riskRegister): void
    {
        $user = auth()->user();

        if ($user->hasRole('prodi') && $riskRegister->academic_program_id !== $user->academic_program_id) {
            abort(403, 'Anda tidak memiliki akses ke profil risiko ini.');
        }

        if ($user->hasRole('unit') && $riskRegister->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki akses ke profil risiko ini.');
        }

        // Auditor hanya dapat mengelola profil risiko prodi/unit yang ditugaskan
        if ($user->hasRole('auditor')) {
            $assigned = AuditAssignment::where('auditor_id', $user->id)
                ->where(function ($q) use ($riskRegister) {
                    $q->where('academic_program_id', $riskRegister->academic_program_id)
                      ->orWhere('unit_id', $riskRegister->unit_id);
                })
                ->exists();
            if (!$assigned) {
                abort(403, 'Anda tidak memiliki akses ke profil risiko ini.');
            }
        }
    }

    /**
     * Pengisian hanya boleh dilakukan Risk Owner (Prodi/Unit).
     * SPMI & Auditor hanya membaca; SPMI memvalidasi lewat action terpisah.
     */
    private function authorizeWrite(): void
    {
        $user = auth()->user();
        if ($user->hasRole('auditor') || $user->hasRole('spmi')) {
            abort(403, 'Risk Register bersifat read-only untuk SPMI/Auditor. Pengisian risiko adalah tugas Program Studi / Unit Kerja.');
        }
    }

    /**
     * Kirim notifikasi database ke seluruh akun Risk Owner (Prodi/Unit) risiko ini.
     */
    private function notifyOwnerRevision(RiskRegister $riskRegister, string $note): void
    {
        $owners = User::query()->where(function ($q) use ($riskRegister) {
            $hasCondition = false;

            if ($riskRegister->academic_program_id) {
                $hasCondition = true;
                $q->where('academic_program_id', $riskRegister->academic_program_id)
                    ->whereHas('roles', fn ($r) => $r->where('name', 'prodi'));
            }

            if ($riskRegister->unit_id) {
                if ($hasCondition) {
                    $q->orWhere(fn ($q2) => $q2->where('unit_id', $riskRegister->unit_id)
                        ->whereHas('roles', fn ($r) => $r->where('name', 'unit')));
                } else {
                    $q->where('unit_id', $riskRegister->unit_id)
                        ->whereHas('roles', fn ($r) => $r->where('name', 'unit'));
                }
            }
        })->get();

        if ($owners->isEmpty()) {
            return;
        }

        Notification::send($owners, new RiskRevisionRequested($riskRegister, $note, auth()->user()->name));
    }
}
