<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AcademicYear;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;

class AuditCycleController extends Controller
{
    public function index()
    {
        $cycles = AuditCycle::with('creator')->withCount('assignments')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.audit.cycles.index', compact('cycles'));
    }

    public function create()
    {
        $academicYears = AcademicYear::active();
        return view('admin.audit.cycles.create', compact('academicYears'));
    }

    public function store(Request $request)
    {
        $this->validateCycle($request);

        AuditCycle::create([
            'name' => $request->name,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
            'description' => $request->description,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.audit.cycles.index')->with('success', 'Siklus AMI berhasil dibuat.');
    }

    public function edit(AuditCycle $cycle)
    {
        $this->ensureEditable($cycle);
        return view('admin.audit.cycles.edit', compact('cycle'));
    }

    public function update(Request $request, AuditCycle $cycle)
    {
        $this->ensureEditable($cycle);
        $this->validateCycle($request, $cycle->id);

        $cycle->update($request->all());

        return redirect()->route('admin.audit.cycles.index')->with('success', 'Siklus AMI berhasil diperbarui.');
    }

    public function destroy(AuditCycle $cycle)
    {
        $this->ensureEditable($cycle);
        if ($cycle->assignments()->count() > 0) {
            return back()->with('error', 'Siklus tidak dapat dihapus karena sudah memiliki alokasi auditor.');
        }

        $cycle->delete();
        return redirect()->route('admin.audit.cycles.index')->with('success', 'Siklus AMI berhasil dihapus.');
    }

    private function validateCycle(Request $request, $ignoreId = null)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => [
                'required', 'string', 'max:10',
                Rule::unique('audit_cycles', 'academic_year')
                    ->where('semester', $request->semester)
                    ->ignore($ignoreId),
            ],
            'semester' => 'required|in:Ganjil,Genap',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:draft,aktif,selesai',
        ], [
            'academic_year.unique' => 'Siklus untuk tahun akademik dan semester tersebut sudah ada. Silakan pilih kombinasi yang lain.',
        ]);
    }

    private function ensureEditable(AuditCycle $cycle)
    {
        if ($cycle->status !== 'draft') {
            abort(403, 'Siklus yang sudah ' . $cycle->status . ' dikunci sebagai riwayat dan tidak dapat diubah/dihapus.');
        }
    }
}
