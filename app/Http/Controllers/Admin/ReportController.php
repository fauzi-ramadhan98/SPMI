<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AcademicProgram;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ReportController extends Controller
{
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
            $reportedAssignments = $this->scopedAssignments($cycleId, $level, $programId);
            $summary = $this->summarize($reportedAssignments);
        }

        return view('admin.reports.index', compact(
            'cycles', 'programs', 'cycleId', 'level', 'programId',
            'summary', 'reportedAssignments', 'isProdi'
        ));
    }

    public function generateAmiPdf(Request $request)
    {
        $request->validate([
            'cycle_id' => 'required|exists:audit_cycles,id',
            'level' => 'required|in:institusi,prodi',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
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

        $assignments = $this->scopedAssignments($cycle->id, $level, $programId);
        $summary = $this->summarize($assignments);

        $program = $level === 'prodi' ? AcademicProgram::find($programId) : null;

        $safeYear = str_replace(['/', '\\'], '-', $cycle->academic_year);
        $filename = 'laporan-ami-'
            . $safeYear . '-'
            . $cycle->semester
            . ($level === 'prodi' ? '-' . Str::slug($program->name ?? 'prodi') : '')
            . '.pdf';

        $pdf = Pdf::loadView('admin.reports.ami_pdf', compact('cycle', 'assignments', 'summary', 'level', 'program'));
        return $pdf->download($filename);
    }

    protected function scopedAssignments(int $cycleId, string $level, ?int $programId = null)
    {
        $query = AuditAssignment::where('audit_cycle_id', $cycleId)
            ->whereIn('status', ['finalisasi', 'selesai']);

        if ($level === 'prodi' && $programId) {
            $query->where('academic_program_id', $programId);
        }

        return $query->with(['academicProgram', 'unit', 'instruments', 'findings'])->get();
    }

    protected function summarize($assignments)
    {
        $instruments = $assignments->flatMap->instruments;
        $findings = $assignments->flatMap->findings;

        return [
            'auditee_count' => $assignments->count(),
            'instrument_count' => $instruments->count(),
            'kts_mayor' => $instruments->filter(fn ($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Mayor'))->count(),
            'kts_minor' => $instruments->filter(fn ($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Minor'))->count(),
            'sesuai' => $instruments->filter(fn ($i) => in_array($i->finding_category, ['Melampaui Standar Nasional', 'Sesuai dengan Standar']))
                ->count(),
            'ob' => $findings->where('type', 'OB')->count(),
            'kts_findings' => $findings->where('type', 'KTS')->count(),
            'total_findings' => $findings->count(),
            'total_instruments' => $instruments->count(),
        ];
    }
}