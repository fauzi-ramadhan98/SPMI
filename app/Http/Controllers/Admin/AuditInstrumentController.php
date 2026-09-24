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

        // Ambil Risk Register auditee (prodi ATAU unit), hanya level High sebagai prioritas untuk panel atas
        $risks = $this->auditeeRisks($assignment)->where('risk_level', 'High')->sortByDesc('risk_score')->values();
        // Semua risiko untuk mapping Level per indikator
        $allRisks = $this->auditeeRisks($assignment);
        $evaluation = $this->auditeeEvaluation($assignment);
        $edItems = $evaluation ? $evaluation->items()->with(['attachments','checklistItem','standard'])->get() : collect();
        $edByIndicator = collect();
        $normalize = function($t){
            $t = trim((string)$t);
            $t = preg_replace('/^\s*\[[^\]]+\]\s*/', '', $t);
            $t = preg_replace('/^\s*(IKU|IKT)\s*\d+\s*[:\.]\s*/i', '', $t);
            $t = preg_replace('/^\s*\d+\.\s*/', '', $t);
            $t = preg_replace('/\s*\(Target:.*$/i', '', $t);
            $t = str_replace('●', '', $t);
            return mb_strtolower(trim($t));
        };
        foreach ($edItems as $it) {
            $key = $normalize($it->indicator);
            if ($key !== '') $edByIndicator[$key] = $it;
            // fallback simpan juga key mentah untuk kompatibilitas
            $rawKey = mb_strtolower(trim($it->indicator));
            if (!isset($edByIndicator[$rawKey])) $edByIndicator[$rawKey] = $it;
        }
        // Mapping Level Risiko per indikator (dari RiskRegister, diambil risk_level)
        $riskByIndicator = collect();
        foreach ($allRisks as $risk) {
            $key = $normalize($risk->butir_tilik);
            if ($key !== '' && !isset($riskByIndicator[$key])) $riskByIndicator[$key] = $risk;
            $rawKey = mb_strtolower(trim($risk->butir_tilik));
            if ($rawKey !== '' && !isset($riskByIndicator[$rawKey])) $riskByIndicator[$rawKey] = $risk;
            // fallback kombinasi standar+butir untuk presisi
            $stdKey = mb_strtolower(trim($risk->standar_mutu ?? '')) . '|' . $key;
            if ($stdKey !== '|' && !isset($riskByIndicator[$stdKey])) $riskByIndicator[$stdKey] = $risk;
        }
        // Simpan closure normalisasi untuk dipakai di view (via share)
        view()->share('normalizeIndicator', $normalize);

        return view('admin.audit.instruments.index', compact('assignment', 'instruments', 'risks', 'edByIndicator', 'evaluation', 'riskByIndicator'));
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
