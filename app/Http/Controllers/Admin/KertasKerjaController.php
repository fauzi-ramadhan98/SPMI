<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditAssignment;
use App\Models\Evaluation;
use App\Models\AcademicProgram;
use App\Models\Unit;

class KertasKerjaController extends Controller
{
    /**
     * Panduan Etik & Kode Etik Auditor Internal.
     */
    public function panduanEtik()
    {
        return view('admin.panduan_etik.index');
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = AuditAssignment::with(['cycle', 'academicProgram', 'unit', 'auditor']);

        // Auditor hanya melihat penugasannya sendiri
        if (!$user->hasAnyRole(['spmi', 'administrator'])) {
            $query->where('auditor_id', $user->id);
        }

        if ($request->filled('cycle_id')) {
            $query->where('audit_cycle_id', $request->cycle_id);
        }

        $assignments = $query->orderBy('audit_cycle_id', 'desc')->get();

        // Petakan ED untuk tiap auditee
        $assignments->each(function ($assignment) {
            if ($assignment->academic_program_id) {
                $assignment->setAttribute('evaluation', Evaluation::where('evaluable_type', AcademicProgram::class)
                    ->where('evaluable_id', $assignment->academic_program_id)
                    ->latest()
                    ->first());
            } elseif ($assignment->unit_id) {
                $assignment->setAttribute('evaluation', Evaluation::where('evaluable_type', Unit::class)
                    ->where('evaluable_id', $assignment->unit_id)
                    ->latest()
                    ->first());
            } else {
                $assignment->setAttribute('evaluation', null);
            }
        });

        $cycles = \App\Models\AuditCycle::orderBy('created_at', 'desc')->get();

        return view('admin.kertas_kerja.index', compact('assignments', 'cycles'));
    }
}