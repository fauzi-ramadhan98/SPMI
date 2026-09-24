<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RtmMeeting;
use App\Models\RtmInstruction;
use App\Models\AuditCycle;
use App\Models\AuditAssignment;
use App\Models\AuditFinding;
use App\Models\AuditInstrument;
use App\Models\AcademicProgram;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class RtmController extends Controller
{
    /**
     * Daftar RTM — role-aware:
     * - SPMI   : operator penuh (CRUD, undang peserta, ketik notulensi, rumuskan instruksi).
     * - Pimpinan: read-only + executive summary + tombol Sahkan.
     * - Prodi/Unit: read-only, hanya risalah yang sudah DISAHKAN & berisi instruksi utk dirinya.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();

        $meetings = RtmMeeting::with(['cycle', 'creator', 'approver', 'instructions', 'participants'])
            ->orderBy('created_at', 'desc');

        if ($user->hasRole('prodi|unit')) {
            $meetings->where('status', 'disahkan')
                ->where(function ($q) use ($user) {
                    if ($user->academic_program_id) {
                        $q->whereHas('instructions', fn ($i) => $i->where('academic_program_id', $user->academic_program_id));
                    }
                    if ($user->unit_id) {
                        $q->orWhereHas('instructions', fn ($i) => $i->where('unit_id', $user->unit_id));
                    }
                });
        }

        $meetings = $meetings->get();

        // Executive summary (pimpinan & spmi)
        $summary = $this->executiveSummary();

        return view('admin.rtm.index', compact('meetings', 'cycles', 'summary'));
    }

    public function create()
    {
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();
        $programs = AcademicProgram::where('is_active', true)->orderBy('degree_level')->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $invitees = User::whereHas('roles', fn ($r) => $r->whereIn('name', ['pimpinan', 'prodi', 'unit']))
            ->with('roles', 'academicProgram', 'unit')
            ->orderBy('name')->get();

        return view('admin.rtm.create', compact('cycles', 'programs', 'units', 'invitees'));
    }

    /**
     * Tarik temuan AMI untuk siklus tertentu → dikembalikan sebagai data terstruktur untuk checklist.
     * Diprioritaskan temuan KTS (mayor/risiko tinggi); disusun berjenjang per auditee.
     */
    public function cycleFindings(AuditCycle $cycle)
    {
        $findings = AuditFinding::whereHas('assignment', fn ($q) => $q->where('audit_cycle_id', $cycle->id))
            ->with(['assignment.academicProgram', 'assignment.unit'])
            ->get()
            ->sortBy(fn ($f) => [$f->assignment->auditee_label, $f->type === 'KTS' ? 0 : 1])
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'type' => $f->type,
                    'criteria' => $f->criteria,
                    'description' => $f->description,
                    'corrective_action' => $f->corrective_action,
                    'status' => $f->status,
                    'auditee_label' => $f->assignment->auditee_label,
                    'auditee_type' => $f->assignment->auditee_type_label,
                ];
            });

        $byAuditee = $findings->groupBy(fn ($f) => $f['auditee_label']);

        $data = [
            'cycle_name' => $cycle->name,
            'findings' => [],
            'total' => $findings->count(),
            'kts' => $findings->where('type', 'KTS')->count(),
        ];

        foreach ($byAuditee as $auditee => $items) {
            $auditeeData = [
                'auditee' => $auditee,
                'items' => []
            ];
            foreach ($items as $finding) {
                $kts = $finding['type'] === 'KTS';
                $label = $kts ? 'KTS' : 'OB';
                $status = match ($finding['status']) {
                    'closed' => 'Ditindaklanjuti',
                    'in_progress' => 'Dalam proses',
                    default => 'Belum ditindaklanjuti',
                };

                $auditeeData['items'][] = [
                    'id' => $finding['id'],
                    'type' => $finding['type'],
                    'criteria' => $finding['criteria'],
                    'description' => $finding['description'],
                    'corrective_action' => $finding['corrective_action'],
                    'status' => $finding['status'],
                    'status_label' => $status,
                    'label' => $label,
                    'auditee_label' => $finding['auditee_label'],
                    'auditee_type' => $finding['auditee_type'],
                ];
            }
            $data['findings'][] = $auditeeData;
        }

        if ($findings->isEmpty()) {
            $data['findings'] = [];
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $data = $this->validateMeeting($request);

        // Jika ada temuan yang dipilih, generate agenda otomatis
        $selectedFindings = $request->input('selected_findings');
        if ($selectedFindings) {
            $selectedIds = explode(',', $selectedFindings);
            $findings = \App\Models\AuditFinding::whereIn('id', $selectedIds)
                ->with(['assignment.academicProgram', 'assignment.unit'])
                ->get()
                ->sortBy(fn ($f) => [$f->assignment->auditee_label, $f->type === 'KTS' ? 0 : 1]);

            $byAuditee = $findings->groupBy(fn ($f) => $f->assignment->auditee_label);

            $lines = [];
            $lines[] = 'Agenda Rapat Tinjauan Manajemen — Siklus ' . \App\Models\AuditCycle::find($data['audit_cycle_id'])->name;
            $lines[] = '(Temuan dipilih oleh SPMI untuk dibahas di RTM)';
            $lines[] = '';

            foreach ($findings->groupBy(fn ($f) => $f->assignment->auditee_label) as $auditee => $items) {
                $lines[] = '[' . $auditee . ']';
                $lines[] = '';
                foreach ($items as $finding) {
                    $kts = $finding->type === 'KTS';
                    $label = $kts ? 'KTS' : 'OB';
                    $status = match ($finding->status) {
                        'closed' => 'Ditindaklanjuti',
                        'in_progress' => 'Dalam proses',
                        default => 'Belum ditindaklanjuti',
                    };

                    $lines[] = $label . ' — ' . $finding->criteria;
                    $lines[] = 'Deskripsi: ' . ($finding->description ?: '(Kosong/Tidak terisi)');
                    if ($finding->corrective_action) {
                        $lines[] = 'Rencana Tindak Lanjut: ' . $finding->corrective_action;
                    }
                    $lines[] = 'Status: ' . $status;
                    $lines[] = '';
                }
            }

            if ($findings->isEmpty()) {
                $lines[] = 'Tidak ada temuan yang dipilih untuk Agenda Rapat.';
            }

            $data['agenda'] = implode(PHP_EOL, $lines);
        }

        $meeting = RtmMeeting::create([
            'audit_cycle_id' => $data['audit_cycle_id'],
            'title' => $data['title'],
            'meeting_date' => $data['meeting_date'] ?? null,
            'meeting_time' => $data['meeting_time'] ?? null,
            'location' => $data['location'] ?? null,
            'agenda' => $data['agenda'] ?? null,
            'status' => 'dijadwalkan',
            'created_by' => auth()->id(),
        ]);

        $meeting->participants()->sync($data['participant_ids'] ?? []);

        return redirect()->route('admin.rtm.show', $meeting->id)
            ->with('success', 'Jadwal RTM "' . $meeting->title . '" berhasil dibuat. Anda dapat menambahkan peserta, mengetik notulensi, dan merumuskan instruksi.');
    }

    public function show(RtmMeeting $rtm)
    {
        $rtm->load(['cycle.assignments.instruments', 'cycle.assignments.findings', 'participants', 'instructions.academicProgram', 'instructions.unit', 'creator', 'approver']);

        $cycleStats = $this->cycleStats($rtm->cycle);
        $invitees = User::whereHas('roles', fn ($r) => $r->whereIn('name', ['pimpinan', 'prodi', 'unit']))
            ->with('roles', 'academicProgram', 'unit')->orderBy('name')->get();
        $programs = AcademicProgram::where('is_active', true)->orderBy('degree_level')->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('admin.rtm.show', compact('rtm', 'cycleStats', 'invitees', 'programs', 'units'));
    }

    public function edit(RtmMeeting $rtm)
    {
        abort_if($rtm->status === 'disahkan', 403, 'RTM yang sudah disahkan dikunci sebagai riwayat.');

        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();
        $invitees = User::whereHas('roles', fn ($r) => $r->whereIn('name', ['pimpinan', 'prodi', 'unit']))
            ->with('roles', 'academicProgram', 'unit')->orderBy('name')->get();
        $rtm->load('participants');

        return view('admin.rtm.edit', compact('rtm', 'cycles', 'invitees'));
    }

    public function update(Request $request, RtmMeeting $rtm)
    {
        abort_if($rtm->status === 'disahkan', 403, 'RTM yang sudah disahkan dikunci sebagai riwayat.');

        $data = $this->validateMeeting($request);

        $rtm->update([
            'audit_cycle_id' => $data['audit_cycle_id'],
            'title' => $data['title'],
            'meeting_date' => $data['meeting_date'] ?? null,
            'meeting_time' => $data['meeting_time'] ?? null,
            'location' => $data['location'] ?? null,
            'agenda' => $data['agenda'] ?? null,
        ]);

        $rtm->participants()->sync($data['participant_ids'] ?? []);

        return redirect()->route('admin.rtm.show', $rtm->id)
            ->with('success', 'Jadwal RTM berhasil diperbarui.');
    }

    public function destroy(RtmMeeting $rtm)
    {
        abort_if($rtm->status === 'disahkan', 403, 'RTM yang sudah disahkan tidak dapat dihapus untuk menjaga keutuhan riwayat.');

        $rtm->delete();

        return redirect()->route('admin.rtm.index')->with('success', 'Jadwal RTM dihapus.');
    }

    /**
     * SPMI mengetik notulensi (risalah) & mengajukan ke pimpinan.
     */
    public function updateNotulensi(Request $request, RtmMeeting $rtm)
    {
        abort_if($rtm->status === 'disahkan', 403, 'RTM sudah disahkan dan tidak dapat diubah.');

        $request->validate([
            'notulensi' => 'nullable|string',
            'status' => 'required|in:dijadwalkan,berlangsung,notulensi',
        ]);

        $rtm->update([
            'notulensi' => $request->notulensi,
            'status' => $request->status,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return back()->with('success', $request->status === 'notulensi'
            ? 'Notulensi selesai & diajukan ke pimpinan untuk disahkan.'
            : 'Notulensi berhasil disimpan.');
    }

    public function storeInstruction(Request $request, RtmMeeting $rtm)
    {
        abort_if($rtm->status === 'disahkan', 403, 'RTM sudah disahkan dan tidak dapat diubah.');

        $request->validate([
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'unit_id' => 'nullable|exists:units,id',
            'instruction' => 'required|string',
            'target_date' => 'nullable|date',
        ]);

        abort_if(!$request->academic_program_id && !$request->unit_id, 403, 'Tentukan target (prodi atau unit) untuk instruksi ini.');

        RtmInstruction::create([
            'rtm_meeting_id' => $rtm->id,
            'academic_program_id' => $request->academic_program_id,
            'unit_id' => $request->unit_id,
            'instruction' => $request->instruction,
            'target_date' => $request->target_date,
        ]);

        return back()->with('success', 'Instruksi tindak lanjut berhasil ditambahkan.');
    }

    public function destroyInstruction(RtmInstruction $instruction)
    {
        $rtm = $instruction->meeting;
        abort_if($rtm->status === 'disahkan', 403, 'RTM sudah disahkan dan tidak dapat diubah.');

        $instruction->delete();

        return back()->with('success', 'Instruksi dihapus.');
    }

    /**
     * Pimpinan menyetujui / membatalkan persetujuan risalah RTM (ketuk palu).
     */
    public function approve(RtmMeeting $rtm)
    {
        if (request('approve')) {
            abort_unless($rtm->status === 'notulensi', 403, 'Notulensi harus selesai & diajukan (Menunggu Pengesahan) sebelum dapat disahkan.');
            $rtm->update([
                'status' => 'disahkan',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return back()->with('success', 'Risalah RTM "' . $rtm->title . '" resmi disahkan. Instruksi kini berlaku & tampil di dasbor auditee.');
        }

        abort_unless($rtm->status === 'disahkan', 403, 'Hanya risalah yang sudah disahkan yang dapat dibatalkan.');
        $rtm->update([
            'status' => 'notulensi',
            'approved_by' => null,
            'approved_at' => null,
        ]);
        return back()->with('success', 'Pengesahan risalah dibatalkan, dikembalikan ke status Menunggu Pengesahan.');
    }

    /**
     * Unduh PDF risalah RTM (SPMI, Pimpinan, auditee penerima instruksi).
     */
    public function pdf(RtmMeeting $rtm)
    {
        $user = auth()->user();

        if ($user->hasRole('prodi|unit')) {
            abort_unless($rtm->status === 'disahkan', 403, 'Risalah tersedia setelah disahkan oleh pimpinan.');
            $hasInstruction = $rtm->instructions->contains(fn ($i) =>
                ($user->academic_program_id && $i->academic_program_id === $user->academic_program_id) ||
                ($user->unit_id && $i->unit_id === $user->unit_id)
            );
            abort_unless($hasInstruction, 403, 'Risalah RTM ini tidak memuat instruksi untuk auditee Anda.');
        }

        $rtm->load(['cycle.assignments.instruments', 'cycle.assignments.findings', 'instructions.academicProgram', 'instructions.unit', 'creator', 'approver']);
        $cycleStats = $this->cycleStats($rtm->cycle);

        $filename = 'risalah-rtm-' . Str::slug($rtm->title) . '.pdf';
        $pdf = Pdf::loadView('admin.rtm.risalah_pdf', compact('rtm', 'cycleStats'));
        return $pdf->download($filename);
    }

    protected function validateMeeting(Request $request): array
    {
        return $request->validate([
            'audit_cycle_id' => 'required|exists:audit_cycles,id',
            'title' => 'required|string|max:255',
            'meeting_date' => 'nullable|date',
            'meeting_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'agenda' => 'nullable|string',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
        ]);
    }

    /**
     * Statistik hasil audit untuk satu siklus (dari instrumen & temuan).
     */
    protected function cycleStats(AuditCycle $cycle): array
    {
        $assignments = $cycle->assignments;
        $instruments = $assignments->flatMap->instruments->filter(fn ($i) => $i->score !== null);
        $findings = $assignments->flatMap->findings;

        return [
            'auditees' => $assignments->count(),
            'scored' => $instruments->count(),
            'avg_score' => $instruments->count() > 0 ? $instruments->avg('score') : null,
            'kts_mayor' => $instruments->filter(fn ($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Mayor'))->count(),
            'kts_minor' => $instruments->filter(fn ($i) => $i->finding_category && str_contains($i->finding_category, 'KTS Minor'))->count(),
            'kts' => $findings->where('type', 'KTS')->count(),
            'ob' => $findings->where('type', 'OB')->count(),
            'total' => $findings->count(),
        ];
    }

    /**
     * Ringkasan eksekutif global (grafik temuan per auditee) utk Pimpinan.
     */
    protected function executiveSummary(): array
    {
        $findingsByProgram = AuditFinding::query()
            ->selectRaw('audit_assignments.academic_program_id as pid, units.name as unit_name, audit_assignments.unit_id as uid, count(*) as total')
            ->join('audit_assignments', 'audit_assignments.id', '=', 'audit_findings.audit_assignment_id')
            ->leftJoin('academic_programs', 'academic_programs.id', '=', 'audit_assignments.academic_program_id')
            ->leftJoin('units', 'units.id', '=', 'audit_assignments.unit_id')
            ->groupBy('audit_assignments.academic_program_id', 'units.name', 'audit_assignments.unit_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        return [
            'total_scored' => AuditInstrument::whereNotNull('score')->count(),
            'avg_score' => AuditInstrument::whereNotNull('score')->avg('score'),
            'total_findings' => AuditFinding::count(),
            'kts' => AuditFinding::where('type', 'KTS')->count(),
            'ob' => AuditFinding::where('type', 'OB')->count(),
            'by_program' => $findingsByProgram,
        ];
    }
}