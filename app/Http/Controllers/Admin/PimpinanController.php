<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditCycle;
use App\Models\AuditFinding;

class PimpinanController extends Controller
{
    /**
     * RTL — Rapat Tindak Lanjut (Modul Pengendalian P3).
     * Monitoring temuan lintas siklus + catatan/instruksi pimpinan.
     */
    public function rtl(Request $request)
    {
        $query = AuditFinding::with('assignment.cycle', 'assignment.academicProgram', 'assignment.unit')
            ->orderBy('created_at', 'desc');

        if ($request->filled('cycle_id')) {
            $query->whereHas('assignment', fn($q) => $q->where('audit_cycle_id', $request->cycle_id));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('criteria', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%")
                    ->orWhere('corrective_action', 'like', "%$q%");
            });
        }

        $findings = $query->paginate(15);
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();

        return view('admin.pimpinan.rtl', compact('findings', 'cycles'));
    }

    /**
     * Simpan catatan/instruksi pimpinan pada temuan (RTL).
     */
    public function rtlNote(Request $request, AuditFinding $finding)
    {
        $request->validate([
            'pimpinan_note' => 'nullable|string',
        ]);

        $finding->update(['pimpinan_note' => $request->pimpinan_note]);

        return back()->with('success', 'Catatan pimpinan untuk temuan berhasil disimpan.');
    }
}
