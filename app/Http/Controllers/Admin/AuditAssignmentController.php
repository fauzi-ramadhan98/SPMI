<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AcademicProgram;
use App\Models\Unit;
use App\Models\User;

class AuditAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditAssignment::with(['cycle', 'academicProgram', 'unit', 'auditor']);

        if ($request->filled('cycle_id')) {
            $query->where('audit_cycle_id', $request->cycle_id);
        }

        $user = auth()->user();

        // Auditor: hanya tugas miliknya
        if ($user->hasRole('auditor')) {
            $query->where('auditor_id', $user->id);
        }

        // Prodi: hanya audit untuk prodi miliknya
        if ($user->hasRole('prodi')) {
            $query->where('academic_program_id', $user->academic_program_id);
        }

        // Unit: hanya audit untuk unit miliknya
        if ($user->hasRole('unit')) {
            $query->where('unit_id', $user->unit_id);
        }

        $assignments = $query->paginate(15);
        $cycles = AuditCycle::all();

        return view('admin.audit.assignments.index', compact('assignments', 'cycles'));
    }

    public function create()
    {
        // Alokasi hanya untuk siklus yang akan datang / sedang berjalan, bukan yang sudah selesai.
        $cycles = AuditCycle::where('status', '!=', 'selesai')->orderBy('created_at', 'desc')->get();
        $programs = AcademicProgram::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        // Daftar user dengan role auditor untuk dipilih
        $auditorUsers = User::role('auditor')->with('academicProgram')->orderBy('name')->get();
        return view('admin.audit.assignments.create', compact('cycles', 'programs', 'units', 'auditorUsers'));
    }

    public function store(Request $request)
    {
        // 1. Ambil nama & NIDN dari DB jika auditor_id ada
        $auditorUser = null;
        if ($request->filled('auditor_id')) {
            $auditorUser = User::find($request->auditor_id);
            if ($auditorUser) {
                $request->merge(['auditor_name' => $auditorUser->name]);
                if ($auditorUser->nidn) {
                    $request->merge(['auditor_nidn' => $auditorUser->nidn]);
                }
            }
        }

        $request->validate([
            'audit_cycle_id'      => 'required|exists:audit_cycles,id',
            'auditor_type'        => 'required|in:Auditor Internal,Auditor External',
            'auditor_role'        => 'required|in:Ketua,Anggota',
            'auditor_id'          => 'required|exists:users,id',
            'auditor_name'        => 'required|string|max:255',
            'auditor_nidn'        => 'nullable|string|max:255',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'unit_id'             => 'nullable|exists:units,id',
        ], [
            'auditor_id.required' => 'Opsi dropdown Akun Auditor wajib dipilih.',
        ]);

        // Wajib memilih salah satu auditee: prodi ATAU unit kerja
        if (!$request->filled('academic_program_id') && !$request->filled('unit_id')) {
            return back()->withErrors(['academic_program_id' => 'Pilih salah satu auditee: Program Studi atau Unit Kerja.'])->withInput();
        }

        // Independensi: auditor tidak boleh mengaudit unit kerja / prodi sendiri
        if ($auditorUser && $request->filled('academic_program_id') && $auditorUser->academic_program_id && $auditorUser->academic_program_id == $request->academic_program_id) {
            return back()->withErrors(['academic_program_id' => 'Auditor tidak dapat mengaudit unit kerjanya sendiri (homebase) untuk menjaga objektivitas.'])->withInput();
        }
        if ($auditorUser && $request->filled('unit_id') && $auditorUser->unit_id && $auditorUser->unit_id == $request->unit_id) {
            return back()->withErrors(['unit_id' => 'Auditor tidak dapat mengaudit unit kerjanya sendiri (homebase) untuk menjaga objektivitas.'])->withInput();
        }

        $data = $request->only(['audit_cycle_id', 'auditor_type', 'auditor_role', 'auditor_id', 'auditor_name', 'auditor_nidn', 'academic_program_id', 'unit_id']);

        // Check unique assignment
        $exists = AuditAssignment::where('audit_cycle_id', $data['audit_cycle_id'])
            ->where('auditor_id', $data['auditor_id'] ?? null)
            ->where('auditor_name', $data['auditor_name'])
            ->where(function ($q) use ($data) {
                $q->where('academic_program_id', $data['academic_program_id'] ?? null)
                    ->orWhere('unit_id', $data['unit_id'] ?? null);
            })
            ->exists();

        if ($exists) {
            return back()->with('error', 'Alokasi auditor untuk auditee ini pada siklus tersebut sudah ada.');
        }

        AuditAssignment::create($data);

        return redirect()->route('admin.audit.assignments.index')->with('success', 'Alokasi Auditor berhasil ditambahkan.');
    }

    public function edit(AuditAssignment $assignment)
    {
        abort(403, 'Status audit berjalan otomatis (system-driven) dan tidak dapat diubah manual.');
    }

    public function update(Request $request, AuditAssignment $assignment)
    {
        abort(403, 'Status audit berjalan otomatis (system-driven) dan tidak dapat diubah manual.');
    }

    /**
     * Workflow finalisasi audit (Alur C):
     *  - auditor  → "Finalisasi Kertas Kerja"  : pending/berlangsung → finalisasi
     *  - SPMI     → "Buka Kembali"             : finalisasi/selesai → berlangsung
     *  - SPMI     → "Sesuai & Tutup" (selesai) : finalisasi → selesai
     */
    public function updateStatus(Request $request, AuditAssignment $assignment)
    {
        $request->validate(['action' => 'required|in:finalisasi,selesai,buka_kembali']);

        $user = auth()->user();
        $action = $request->action;

        // Auditor: hanya dapat memfinalisasi tugas miliknya dari status belum dikunci
        if ($user->hasRole('auditor')) {
            abort_if($assignment->auditor_id !== $user->id, 403, 'Anda hanya dapat mengelola kertas kerja tugas Anda sendiri.');
            abort_unless($action === 'finalisasi', 403, 'Auditor hanya dapat melakukan Finalisasi Kertas Kerja.');
            abort_unless(in_array($assignment->status, ['pending', 'berlangsung']), 403, 'Kertas kerja sudah dikunci dan tidak dapat difinalisasi ulang.');
            $assignment->update(['status' => 'finalisasi']);
            return back()->with('success', 'Kertas Kerja telah difinalisasi. Prodi/Unit akan melihat hasil audit sesuai kewenangan.');
        }

        // SPMI: memegang status selesai & dapat membuka kembali kertas yang terkunci
        if ($user->hasRole('spmi') || $user->hasRole('administrator')) {
            if ($action === 'selesai') {
                abort_unless($assignment->status === 'finalisasi', 403, 'Hanya kertas kerja berstatus Finalisasi yang dapat ditutup menjadi Selesai.');
                $assignment->update(['status' => 'selesai']);
                return back()->with('success', 'Hasil audit ditetapkan Selesai. Laporan AMI kini dapat diterbitkan.');
            }

            if ($action === 'buka_kembali') {
                abort_unless(in_array($assignment->status, ['finalisasi', 'selesai']), 403, 'Kertas kerja belum dikunci sehingga tidak perlu dibuka kembali.');
                $assignment->update(['status' => 'berlangsung']);
                return back()->with('success', 'Kertas kerja dibuka kembali menjadi Berlangsung untuk perbaikan auditor.');
            }

            abort(403, 'Aksi status tidak valid.');
        }

        abort(403, 'Anda tidak memiliki kewenangan mengubah status kertas kerja ini.');
    }

    public function destroy(AuditAssignment $assignment)
    {
        if ($assignment->status !== 'pending') {
            abort(403, 'Alokasi yang sudah ' . $assignment->status . ' tidak dapat dihapus untuk menjaga keutuhan data kertas kerja.');
        }
        $assignment->delete();
        return redirect()->route('admin.audit.assignments.index')->with('success', 'Alokasi Auditor berhasil dihapus.');
    }
}
