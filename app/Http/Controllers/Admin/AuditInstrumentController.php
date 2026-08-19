<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditAssignment;
use App\Models\AuditInstrument;
use App\Models\QualityStandard;
use App\Models\RiskRegister;
use App\Models\Evaluation;
use App\Models\AcademicProgram;
use App\Models\Unit;

class AuditInstrumentController extends Controller
{
    public function index(AuditAssignment $assignment)
    {
        // Prodi/Unit hanya boleh melihat penugasan audit miliknya
        $this->authorizeView($assignment);

        $instruments = $assignment->instruments;

        // Ambil Risk Register auditee (prodi ATAU unit), hanya level High sebagai prioritas
        $risks = $this->auditeeRisks($assignment)->where('risk_level', 'High')->sortByDesc('risk_score')->values();

        // Evaluasi Diri auditee untuk dibandingkan auditor (read-only)
        $evaluation = $this->auditeeEvaluation($assignment);
        $edItems = $evaluation ? $evaluation->items()->with('attachments')->get() : collect();
        $edByIndicator = collect();
        foreach ($edItems as $it) {
            $edByIndicator[mb_strtolower(trim($it->indicator))] = $it;
        }

        return view('admin.audit.instruments.index', compact('assignment', 'instruments', 'risks', 'edByIndicator'));
    }

    public function store(Request $request, AuditAssignment $assignment)
    {
        $this->assertMutable($assignment);

        $request->validate([
            'criteria' => 'required|string|max:255',
            'indicator' => 'required|string',
            'score' => 'nullable|in:4,3,2,1,0',
        ]);

        AuditInstrument::create([
            'audit_assignment_id' => $assignment->id,
            'criteria' => $request->criteria,
            'indicator' => $request->indicator,
            'score' => $request->score,
            'finding' => $request->finding,
            'recommendation' => $request->recommendation,
            'finding_category' => $request->finding_category,
        ]);

        return back()->with('success', 'Instrumen audit berhasil ditambahkan.');
    }

    public function update(Request $request, AuditInstrument $instrument)
    {
        $this->assertMutable($instrument->assignment);

        $request->validate([
            'score' => 'nullable|in:4,3,2,1,0',
            'finding_category' => 'nullable|string|max:255',
            'findings' => 'nullable|array',
            'document_links' => 'nullable|array',
            'document_files' => 'nullable|array',
            'document_files.*' => 'nullable|file|max:2048', // max 2MB
        ]);

        $findingsData = [];
        $findings = $request->input('findings', []);
        $links = $request->input('document_links', []);
        $files = $request->file('document_files', []);
        
        $oldFindingsData = $instrument->findings_data ?? [];

        foreach ($findings as $i => $findingText) {
            $link = $links[$i] ?? null;
            $file = $files[$i] ?? null;
            $oldFilePath = $oldFindingsData[$i]['file_path'] ?? null;
            
            $filePath = $oldFilePath;
            if ($file) {
                $filePath = $file->store('audit_documents', 'public');
            }

            if (!empty($findingText) || !empty($link) || !empty($filePath)) {
                $findingsData[] = [
                    'finding' => $findingText,
                    'document_link' => $link,
                    'file_path' => $filePath,
                ];
            }
        }

        $instrument->update([
            'score' => $request->score,
            'finding_category' => $request->finding_category,
            'finding' => $findingsData[0]['finding'] ?? null,
            'document_link' => $findingsData[0]['document_link'] ?? null,
            'findings_data' => count($findingsData) > 0 ? $findingsData : null,
        ]);

        return back()->with('success', 'Nilai instrumen berhasil diperbarui.');
    }

    public function destroy(AuditInstrument $instrument)
    {
        $this->assertMutable($instrument->assignment);

        $instrument->delete();
        return back()->with('success', 'Instrumen berhasil dihapus.');
    }

    public function syncFromRisk(Request $request, AuditAssignment $assignment)
    {
        $this->assertMutable($assignment);

        $risks = $this->auditeeRisks($assignment)->where('risk_level', 'High');

        $count = 0;
        foreach ($risks as $risk) {
            $exists = AuditInstrument::where('audit_assignment_id', $assignment->id)
                ->where('criteria', $risk->standar_mutu)
                ->where('indicator', $risk->butir_tilik)
                ->exists();

            if (!$exists) {
                AuditInstrument::create([
                    'audit_assignment_id' => $assignment->id,
                    'criteria' => $risk->standar_mutu ?? 'Standar Tidak Diberikan',
                    'indicator' => $risk->butir_tilik ?? 'Indikator Tidak Diberikan',
                ]);
                $count++;
            }
        }

        return back()->with('success', "$count standar/indikator berhasil ditarik otomatis dari Profil Risiko.");
    }

    /**
     * Generate instrumen daftar tilik dari master checklist_items (semua standar aktif).
     */
    public function generateFromMaster(Request $request, AuditAssignment $assignment)
    {
        $this->assertMutable($assignment);

        $standards = QualityStandard::with(['checklistItems' => fn($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($standards as $standard) {
            foreach ($standard->checklistItems as $item) {
                $exists = AuditInstrument::where('audit_assignment_id', $assignment->id)
                    ->where('criteria', $standard->kode_standar ?? $standard->name)
                    ->where('indicator', $item->indicator)
                    ->exists();

                if (!$exists) {
                    AuditInstrument::create([
                        'audit_assignment_id' => $assignment->id,
                        'checklist_item_id' => $item->id,
                        'criteria' => $standard->kode_standar ?: ($standard->name ?: 'Standar Tidak Diberikan'),
                        'indicator' => $item->indicator,
                        'audit_question' => $item->audit_question,
                        'rubric_4' => $item->rubric_4,
                        'rubric_3' => $item->rubric_3,
                        'rubric_2' => $item->rubric_2,
                        'rubric_1' => $item->rubric_1,
                        'evidence_document' => $item->evidence_document,
                    ]);
                    $count++;
                }
            }
        }

        if ($count === 0) {
            return back()->with('info', 'Tidak ada butir baru dari master daftar tilik (sudah lengkap atau master kosong).');
        }

        return back()->with('success', "$count butir daftar tilik berhasil digenerate dari master Standar Mutu.");
    }

    /**
     * Risk register milik auditee penugasan (prodi ATAU unit kerja).
     */
    private function auditeeRisks(AuditAssignment $assignment)
    {
        $query = RiskRegister::query();
        if ($assignment->academic_program_id) {
            $query->where('academic_program_id', $assignment->academic_program_id);
        } elseif ($assignment->unit_id) {
            $query->where('unit_id', $assignment->unit_id);
        } else {
            return collect();
        }
        return $query->get();
    }

    /**
     * Evaluasi Diri terbaru milik auditee penugasan (prodi ATAU unit kerja).
     */
    private function auditeeEvaluation(AuditAssignment $assignment)
    {
        if ($assignment->academic_program_id) {
            return Evaluation::where('evaluable_type', AcademicProgram::class)
                ->where('evaluable_id', $assignment->academic_program_id)
                ->latest()
                ->first();
        }
        if ($assignment->unit_id) {
            return Evaluation::where('evaluable_type', Unit::class)
                ->where('evaluable_id', $assignment->unit_id)
                ->latest()
                ->first();
        }
        return null;
    }

    /**
     * Prodi/Unit hanya dapat mengakses penugasan audit miliknya.
     */
    private function authorizeView(AuditAssignment $assignment): void
    {
        $user = auth()->user();

        if ($user->hasRole('prodi') && $assignment->academic_program_id !== $user->academic_program_id) {
            abort(403, 'Anda tidak memiliki akses ke penugasan audit ini.');
        }

        if ($user->hasRole('unit') && $assignment->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki akses ke penugasan audit ini.');
        }
    }

    /**
     * Borang terkunci pada status finalisasi/selesai — tidak boleh diubah/ditulis lagi.
     */
    private function assertMutable(AuditAssignment $assignment): void
    {
        abort_if(in_array($assignment->status, ['finalisasi', 'selesai']), 403, 'Kertas kerja telah dikunci (status ' . $assignment->status . '). Buka kembali melalui SPMI untuk melakukan perubahan.');
    }
}
