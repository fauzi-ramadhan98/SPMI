<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AcademicProgram;
use App\Models\Evaluation;
use App\Models\Unit;
use App\Services\AmiReportService;

class ReportController extends Controller
{
    public function __construct(private AmiReportService $reports)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isProdi = $user->hasRole('prodi');

        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();

        $programs = AcademicProgram::query()
            ->when($isProdi && $user->academic_program_id, fn ($q) => $q->where('id', $user->academic_program_id))
            ->orderBy('degree_level')->orderBy('name')->get();

        $cycleId = $request->filled('cycle_id') ? (int) $request->input('cycle_id') : ($cycles->first()->id ?? null);
        $level = $isProdi
            ? 'prodi'
            : ($request->filled('level') && in_array($request->input('level'), ['institusi', 'prodi']) ? $request->input('level') : 'institusi');
        $programId = $isProdi
            ? $user->academic_program_id
            : ($request->filled('academic_program_id') ? (int) $request->input('academic_program_id') : null);

        $summary = null;
        $reportedAssignments = collect();

        if ($cycleId) {
            $reportedAssignments = $this->reports->scopedAssignments($cycleId, $level, $programId);
            $summary = $this->reports->summarize($reportedAssignments);
        }

        return view('admin.reports.index', compact(
            'cycles', 'programs', 'cycleId', 'level', 'programId',
            'summary', 'reportedAssignments', 'isProdi'
        ));
    }

    /**
     * Cetak cepat: render badan laporan lalu langsung unduh.
     * Jalur Generate Laporan (daftar + lampiran terurut + arsip) ada di
     * ReportBuilderController — keduanya memakai AmiReportService agar isi laporan sama.
     */
    public function generateAmiPdf(Request $request)
    {
        $request->validate([
            'cycle_id' => 'required|exists:audit_cycles,id',
            'level' => 'required|in:institusi,prodi',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            // Poin catatan client: pilih 1 dari 2 format laporan AMI
            'jenis' => 'nullable|in:klasik,berbasis-risiko',
        ]);

        $user = auth()->user();
        $isProdi = $user->hasRole('prodi');

        $cycle = AuditCycle::findOrFail($request->input('cycle_id'));
        $level = $isProdi ? 'prodi' : $request->input('level');
        $programId = $isProdi
            ? $user->academic_program_id
            : ($request->filled('academic_program_id') ? (int) $request->input('academic_program_id') : null);

        if ($level === 'prodi' && !$programId) {
            return back()->withErrors(['academic_program_id' => 'Pilih program studi terlebih dahulu.']);
        }

        $jenis = $request->input('jenis') ?: 'klasik';
        $program = $level === 'prodi' ? AcademicProgram::find($programId) : null;

        $data = $this->reports->assembleData($cycle, $level, $programId, $jenis);
        $body = $this->reports->renderBody($data, false);
        $filename = $this->reports->filename($cycle, $level, $program, $jenis);

        return response($body, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function reviewDetail(AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        $assignment->load(['academicProgram', 'unit', 'cycle', 'instruments', 'findings', 'auditor']);
        // Evaluasi Diri auditee
        $evaluation = null;
        if ($assignment->academic_program_id) {
            $evaluation = Evaluation::where('evaluable_type', AcademicProgram::class)->where('evaluable_id', $assignment->academic_program_id)->latest()->first();
        } elseif ($assignment->unit_id) {
            $evaluation = Evaluation::where('evaluable_type', Unit::class)->where('evaluable_id', $assignment->unit_id)->latest()->first();
        }
        if ($evaluation) $evaluation->load(['items.attachments', 'items.standard', 'items.checklistItem']);
        // Risiko tinggi terkait
        $risks = collect();
        if ($assignment->academic_program_id) {
            $risks = \App\Models\RiskRegister::where('academic_program_id', $assignment->academic_program_id)->where('risk_level', 'High')->get();
        } elseif ($assignment->unit_id) {
            $risks = \App\Models\RiskRegister::where('unit_id', $assignment->unit_id)->where('risk_level', 'High')->get();
        }
        // RTL jika ada (dari RtmMeeting)
        $rtl = null;
        if ($assignment->audit_cycle_id) {
            $rtl = \App\Models\RtmMeeting::where('audit_cycle_id', $assignment->audit_cycle_id)->latest()->first();
        }
        return view('admin.reports.review', compact('assignment', 'evaluation', 'risks', 'rtl'));
    }

    public function approveLha(AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        $assignment->update(['status' => 'selesai']);
        return back()->with('success', 'LHA untuk ' . $assignment->auditee_label . ' disetujui (status: selesai).');
    }

    public function reminderAuditor(AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        // Notifikasi sederhana ke auditor (jika ada)
        if ($assignment->auditor) {
            $assignment->auditor->notify(new \Illuminate\Notifications\DatabaseNotification([
                'type' => 'audit_reminder',
                'assignment_id' => $assignment->id,
            ]));
        }
        return back()->with('success', 'Reminder dikirim ke auditor ' . ($assignment->auditor_name ?? '—') . ' untuk ' . $assignment->auditee_label . '.');
    }

    public function decideRtl(Request $request, AuditAssignment $assignment)
    {
        abort_unless(auth()->user()->hasAnyRole(['spmi', 'administrator']), 403);
        $request->validate(['decision' => 'required|in:setujui,tolak,eskalasi', 'catatan' => 'nullable|string|max:2000']);
        $decision = $request->input('decision');
        if ($decision === 'setujui') {
            $assignment->update(['status' => 'selesai']);
            $msg = 'RTL disetujui. LHA disahkan.';
        } elseif ($decision === 'tolak') {
            $assignment->update(['status' => 'berlangsung']);
            $msg = 'RTL ditolak, diminta revisi. Status dikembalikan ke berlangsung.';
        } else {
            // eskalasi ke RTM - buat entri RTM jika belum ada
            $rtm = \App\Models\RtmMeeting::firstOrCreate(
                ['audit_cycle_id' => $assignment->audit_cycle_id],
                ['title' => 'RTM Eskalasi - ' . $assignment->auditee_label, 'status' => 'draft', 'created_by' => auth()->id()]
            );
            $msg = 'Dieskalasi ke RTM (ID: ' . $rtm->id . '). Rektor akan memutuskan.';
        }
        // Simpan catatan jika ada
        if ($request->filled('catatan')) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'spmi_decide_rtl_' . $decision,
                'description' => 'SPMI ' . $decision . ' RTL untuk ' . $assignment->auditee_label . ': ' . $request->input('catatan'),
                'loggable_type' => AuditAssignment::class,
                'loggable_id' => $assignment->id,
            ]);
        }
        return back()->with('success', $msg);
    }
}
