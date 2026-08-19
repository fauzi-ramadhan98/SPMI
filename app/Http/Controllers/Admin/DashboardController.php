<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AuditFinding;
use App\Models\Evaluation;
use App\Models\RiskRegister;
use App\Models\User;
use App\Models\Unit;
use App\Models\AcademicProgram;
use App\Models\ActivityLog;
use App\Models\RtmMeeting;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('administrator')) {
            return $this->systemDashboard();
        }
        if ($user->hasRole('pimpinan')) {
            return $this->executiveDashboard();
        }
        if ($user->hasRole('spmi')) {
            return $this->institusiDashboard();
        }
        if ($user->hasRole('auditor')) {
            return $this->auditorDashboard();
        }
        if ($user->hasRole('prodi|unit')) {
            return $this->prodiDashboard();
        }

        // Fallback generik
        return $this->genericDashboard();
    }

    /**
     * Super Admin — Dashboard Sistem (memori, log error, aktivitas pengguna).
     */
    protected function systemDashboard()
    {
        $users = User::with('roles')->orderBy('name')->get();
        $usersByRole = $users->flatMap->roles->groupBy('name')->map->count();
        $totalUsers = $users->count();

        $recentActivity = ActivityLog::latestFirst()->with('user')->take(15)->get();

        $totalDocuments = Document::count();
        $totalPrograms = AcademicProgram::where('is_active', true)->count();
        $totalUnits = Unit::where('is_active', true)->count();
        $totalAuditors = User::role('auditor')->count();
        $activeCycles = AuditCycle::where('status', 'aktif')->count();
        $totalLogs = ActivityLog::count();

        return view('admin.dashboards.system', compact(
            'usersByRole', 'totalUsers', 'recentActivity', 'totalDocuments', 'totalPrograms',
            'totalUnits', 'totalAuditors', 'activeCycles', 'totalLogs'
        ));
    }

    /**
     * SPMI — Dashboard Mutu Institusi (agregat capaian seluruh prodi).
     */
    protected function institusiDashboard()
    {
        $programs = AcademicProgram::where('is_active', true)->with(['evaluations'])->get();
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();
        $activeCycles = AuditCycle::where('status', 'aktif')->count();
        $totalDocuments = Document::where('module', 'dokumen_mutu')->count();

        return view('admin.dashboards.institusi', compact(
            'programs', 'cycles', 'activeCycles', 'totalDocuments'
        ));
    }

    /**
     * Auditee (Prodi / Unit) — Dashboard Kinerja.
     */
    protected function prodiDashboard()
    {
        $user = auth()->user();
        $evaluableType = $user->academicProgram ? 'App\\Models\\AcademicProgram' : 'App\\Models\\Unit';
        $evaluableId = $user->academicProgram?->id ?? $user->unit?->id;

        $evaluations = Evaluation::where('evaluable_type', $evaluableType)
            ->where('evaluable_id', $evaluableId)->latest()->get();

        $risks = RiskRegister::where(function ($q) use ($user) {
            if ($user->academic_program_id) $q->where('academic_program_id', $user->academic_program_id);
            if ($user->unit_id) $q->where('unit_id', $user->unit_id);
        })->get();

        $assignments = AuditAssignment::query()
            ->where(function ($q) use ($user) {
                if ($user->academic_program_id) $q->where('academic_program_id', $user->academic_program_id);
                if ($user->unit_id) $q->where('unit_id', $user->unit_id);
            })
            ->with('cycle')->get();

        $findingIds = $assignments->pluck('id')->all();
        $openFindings = AuditFinding::whereIn('audit_assignment_id', $findingIds)->whereIn('status', ['open', 'in_progress'])->count();

        $notifications = $user->notifications()->latest()->take(10)->get();
        $unreadNotifications = $user->unreadNotifications()->count();

        $rtmMinutes = RtmMeeting::where('status', 'disahkan')
            ->where(fn ($q) => $q->whereHas('instructions', fn ($i) =>
                $user->academic_program_id ? $i->where('academic_program_id', $user->academic_program_id) : $i->whereRaw('1=0')
            )->orWhereHas('instructions', fn ($i) =>
                $user->unit_id ? $i->where('unit_id', $user->unit_id) : $i->whereRaw('1=0')
            ))
            ->with('cycle', 'instructions')
            ->latest()
            ->get();

        return view('admin.dashboards.prodi', compact('evaluations', 'risks', 'assignments', 'openFindings', 'notifications', 'unreadNotifications', 'rtmMinutes'));
    }

    /**
     * Auditor — Dashboard Auditor (daftar tugas).
     */
    protected function auditorDashboard()
    {
        $user = auth()->user();
        $assignments = AuditAssignment::where('auditor_id', $user->id)
            ->with(['cycle', 'academicProgram', 'unit'])->get();

        $assignmentIds = $assignments->pluck('id')->all();
        $pendingFindings = AuditFinding::whereIn('audit_assignment_id', $assignmentIds)
            ->whereIn('status', ['open', 'in_progress'])->count();

        return view('admin.dashboards.auditor', compact('assignments', 'assignmentIds', 'pendingFindings'));
    }

    /**
     * Pimpinan — Executive Dashboard (BI) + peta risiko.
     */
    protected function executiveDashboard()
    {
        $cycles = AuditCycle::where('status', 'aktif')->latest()->take(5)->get();
        $findingsCount = AuditFinding::count();
        $openFindings = AuditFinding::whereIn('status', ['open', 'in_progress'])->count();
        $evaluationsSubmitted = Evaluation::where('status', '!=', 'draft')->count();

        // Peta Risiko: matriks 5x5 (probabilitas × dampak)
        $riskMatrix = [];
        $riskAll = RiskRegister::whereNotNull('probability')->whereNotNull('impact')->get();
        for ($p = 1; $p <= 5; $p++) {
            for ($i = 1; $i <= 5; $i++) {
                $riskMatrix[$p][$i] = $riskAll->filter(fn ($r) => $r->probability === $p && $r->impact === $i)->count();
            }
        }

        // Temuan per program studi (melalui assignment)
        $findingsByProgram = AuditFinding::query()
            ->selectRaw('audit_assignments.academic_program_id as pid, units.name as unit_name, audit_assignments.unit_id as uid, count(*) as total')
            ->join('audit_assignments', 'audit_assignments.id', '=', 'audit_findings.audit_assignment_id')
            ->leftJoin('academic_programs', 'academic_programs.id', '=', 'audit_assignments.academic_program_id')
            ->leftJoin('units', 'units.id', '=', 'audit_assignments.unit_id')
            ->groupBy('audit_assignments.academic_program_id', 'units.name', 'audit_assignments.unit_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $programCount = $riskAll->whereNotNull('academic_program_id')->count();

        return view('admin.dashboards.executive', compact(
            'cycles', 'findingsCount', 'openFindings', 'evaluationsSubmitted',
            'riskMatrix', 'findingsByProgram', 'programCount'
        ));
    }

    /**
     * Fallback generik (semua data umum).
     */
    protected function genericDashboard()
    {
        $documentQuery = Document::query();
        $totalDocuments = (clone $documentQuery)->count();
        $recentDocuments = Document::with(['uploader'])->orderBy('created_at', 'desc')->take(5)->get();
        $totalAuditors = User::role('auditor')->count();
        $totalUnits = Unit::where('is_active', true)->count();
        $activeCycles = AuditCycle::where('status', 'aktif')->count();

        return view('admin.dashboard', compact(
            'totalDocuments', 'recentDocuments', 'totalAuditors', 'totalUnits', 'activeCycles'
        ));
    }
}