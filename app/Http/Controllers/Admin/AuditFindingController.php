<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditAssignment;
use App\Models\AuditFinding;

class AuditFindingController extends Controller
{
    public function index(AuditAssignment $assignment)
    {
        // Prodi/Unit hanya boleh mengakses penugasan audit miliknya
        $user = auth()->user();
        if ($user->hasRole('prodi') && $assignment->academic_program_id !== $user->academic_program_id) {
            abort(403, 'Anda tidak memiliki akses ke temuan audit ini.');
        }
        if ($user->hasRole('unit') && $assignment->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki akses ke temuan audit ini.');
        }

        $findings    = $assignment->findings;
        $instruments = $assignment->instruments()->whereNotNull('criteria')->orderBy('id')->get();
        return view('admin.audit.findings.index', compact('assignment', 'findings', 'instruments'));
    }

    public function store(Request $request, AuditAssignment $assignment)
    {
        $this->assertMutable($assignment);

        $request->validate([
            'type' => 'required|in:KTS,OB',
            'criteria' => 'required|string',
            'description' => 'required|string',
        ]);

        AuditFinding::create([
            'audit_assignment_id' => $assignment->id,
            'type'               => $request->type,
            'criteria'           => $request->criteria,
            'description'        => $request->description,
            'root_cause'         => $request->root_cause,
            'corrective_action'  => $request->corrective_action,
            'target_date'        => $request->target_date,
            'status'             => $request->status ?? 'open',
        ]);

        // Auto-set assignment to 'berlangsung' if it's still pending
        if ($assignment->status === 'pending') {
            $assignment->update(['status' => 'berlangsung']);
        }

        return back()->with('success', 'Temuan audit berhasil ditambahkan.');
    }

    public function update(Request $request, AuditFinding $finding)
    {
        $user = auth()->user();
        $isMutuRole = $user->hasAnyRole(['spmi', 'auditor']);
        $isAuditee = $user->hasAnyRole(['prodi', 'unit']);

        $rules = [
            'prodi_clarification' => 'nullable|string',
            'root_cause'          => 'nullable|string',
            'corrective_action'   => 'nullable|string',
            'target_date'         => 'nullable|date',
        ];

        // Auditee (Prodi/Unit) dapat memberikan keputusan atas temuan (setuju/tolak)
        if ($isAuditee) {
            $rules['prodi_decision']      = 'nullable|in:setuju,tolak';
            $rules['prodi_decision_note'] = 'required_if:prodi_decision,tolak|nullable|string';
        }

        // Hanya SPMI dan auditor yang dapat mengubah status secara eksplisit
        if ($isMutuRole) {
            $rules['status'] = 'required|in:open,in_progress,closed,verified';
        }

        $request->validate($rules);

        $updateData = [
            'prodi_clarification' => $request->prodi_clarification,
            'root_cause'          => $request->root_cause,
            'corrective_action'   => $request->corrective_action,
            'target_date'         => $request->target_date,
        ];

        if ($isAuditee) {
            if ($request->filled('prodi_decision')) {
                $updateData['prodi_decision']      = $request->prodi_decision;
                $updateData['prodi_decision_note'] = $request->prodi_decision_note;
                $updateData['prodi_decision_at']   = now();
            }
        }

        if ($isMutuRole) {
            $updateData['status'] = $request->status;
        } elseif ($isAuditee) {
            // Auditee yang memperbarui dan status masih open → otomatis in_progress
            if ($finding->status === 'open' && ($request->filled('prodi_clarification') || $request->filled('root_cause') || $request->filled('corrective_action') || $request->filled('prodi_decision'))) {
                $updateData['status'] = 'in_progress';
            }
        }

        $finding->update($updateData);

        // Auto-update assignment status — use fresh DB queries to avoid Eloquent cache
        $assignmentId = $finding->audit_assignment_id;
        $assignment   = AuditAssignment::find($assignmentId);
        $allFindings  = AuditFinding::where('audit_assignment_id', $assignmentId)->get();

        if ($allFindings->count() > 0) {
            $allDone = $allFindings->every(fn($f) => in_array($f->status, ['closed', 'verified']));
            $anyOpen = $allFindings->contains(fn($f) => in_array($f->status, ['open', 'in_progress']));

            if ($allDone) {
                // All findings resolved/verified → siap difinalisasi (menunggu SPMI menetapkan selesai)
                $assignment->update(['status' => 'finalisasi']);
            } elseif (in_array($assignment->status, ['finalisasi', 'selesai']) && $anyOpen) {
                // A finding was re-opened → revert assignment to berlangsung
                $assignment->update(['status' => 'berlangsung']);
            }
        }

        return back()->with('success', 'Tindak lanjut temuan berhasil diperbarui.');
    }

    public function destroy(AuditFinding $finding)
    {
        $assignment = AuditAssignment::findOrFail($finding->audit_assignment_id);
        $this->assertMutable($assignment);

        $finding->delete();
        return back()->with('success', 'Temuan berhasil dihapus.');
    }

    /**
     * Temuan tidak boleh diubah lagi saat kertas kerja terkunci (finalisasi/selesai).
     */
    private function assertMutable(AuditAssignment $assignment): void
    {
        abort_if(in_array($assignment->status, ['finalisasi', 'selesai']), 403, 'Kertas kerja telah dikunci (status ' . $assignment->status . '). Buka kembali melalui SPMI untuk melakukan perubahan.');
    }
}
