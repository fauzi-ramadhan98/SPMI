<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use Barryvdh\DomPDF\Facade\Pdf;

class SuratTugasController extends Controller
{
    /**
     * Halaman manajemen surat tugas & jadwal visitasi per siklus.
     */
    public function index(Request $request)
    {
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();
        $cycleId = $request->get('cycle_id');

        $query = AuditAssignment::with(['cycle', 'academicProgram', 'unit', 'auditor']);
        $user = auth()->user();
        if (!$user->hasAnyRole(['spmi', 'administrator'])) {
            $query->where('auditor_id', $user->id);
        }
        if ($cycleId) {
            $query->where('audit_cycle_id', $cycleId);
        }
        $assignments = $query->orderBy('audit_cycle_id', 'desc')->get();

        return view('admin.surat_tugas.index', compact('cycles', 'cycleId', 'assignments'));
    }

/**
     * Generate & unduh Surat Tugas Auditor per assignment (PDF).
     * SPMI/admin semua; auditor hanya penugasannya sendiri.
     */
    public function generate(Request $request, AuditAssignment $assignment)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['spmi', 'administrator'])) {
            if (!$user->hasRole('auditor') || $assignment->auditor_id !== $user->id) {
                abort(403, 'Anda hanya dapat mengunduh Surat Tugas milik Anda.');
            }
        }

        $assignment->load(['cycle', 'academicProgram', 'unit', 'auditor']);

        $pdf = Pdf::loadView('admin.surat_tugas.pdf', [
            'assignment' => $assignment,
            'kop'        => $this->kopDataUri(),
            'ttd'        => $this->ttdDataUri(),
        ])->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_left' => 0,
                'margin_right' => 0,
            ]);

        $safeName = str_replace(['/', '\\', ' '], '-', $assignment->auditee_label);

        return $pdf->download('surat-tugas-' . $safeName . '.pdf');
    }

    /**
     * Generate & unduh jadwal visitasi seluruh assignment pada satu siklus (PDF).
     */
    public function generateSchedule(Request $request, AuditCycle $cycle)
    {
        $cycle->load(['assignments.academicProgram', 'assignments.unit', 'assignments.auditor']);

        $pdf = Pdf::loadView('admin.surat_tugas.schedule_pdf', [
            'cycle' => $cycle,
            'kop'   => $this->kopDataUri(),
            'ttd'   => $this->ttdDataUri(),
        ])->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_left' => 0,
                'margin_right' => 0,
            ]);

        $safeCycleName = str_replace(['/', '\\'], '-', $cycle->name);
        $safeAcademicYear = str_replace(['/', '\\'], '-', $cycle->academic_year);

        return $pdf->download('jadwal-visitasi-' . $safeCycleName . '-' . $safeAcademicYear . '.pdf');
    }

    private function kopDataUri(): ?string
    {
        $path = public_path('images/kopstmik.jpg');
        return is_file($path) ? $this->toDataUri($path) : null;
    }

    private function ttdDataUri(): ?string
    {
        $path = public_path('images/TTD-KETUA.jpg');
        return is_file($path) ? $this->toDataUri($path) : null;
    }

    private function toDataUri(string $path): string
    {
        $type = mime_content_type($path);
        return 'data:' . $type . ';base64,' . base64_encode(file_get_contents($path));
    }
}
