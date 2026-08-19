<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicProgram;
use App\Models\Unit;
use App\Models\QualityStandard;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\EvaluationItem;
use App\Models\EvaluationAttachment;
use App\Models\RiskRegister;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Daftar Evaluasi Diri.
     * SPMI/admin/auditor/pimpinan lihat semua; prodi/unit lihat miliknya.
     * Pengisi: prodi/unit (target sendiri) & SPMI/admin (semua target).
     */
    public function index()
    {
        $user = Auth::user();
        $query = Evaluation::with(['evaluable', 'creator'])->orderBy('created_at', 'desc');

        if ($user->hasRole('prodi')) {
            $query->where('evaluable_type', AcademicProgram::class)
                  ->where('evaluable_id', $user->academic_program_id);
        } elseif ($user->hasRole('unit')) {
            $query->where('evaluable_type', Unit::class)
                  ->where('evaluable_id', $user->unit_id);
        }

        $evaluations = $query->paginate(10);
        $canCreate = $this->canCreate();

        return view('admin.evaluations.index', compact('evaluations', 'canCreate'));
    }

    /**
     * Form membuat ED. Prodi/Unit target otomatis diri sendiri;
     * SPMI/mana memilih target.
     */
    public function create()
    {
        if (!$this->canCreate()) {
            abort(403);
        }

        $user = auth()->user();
        if ($user->hasRole('prodi')) {
            return view('admin.evaluations.create_prodi');
        }
        if ($user->hasRole('unit')) {
            return view('admin.evaluations.create_prodi');
        }

        $programs = AcademicProgram::where('is_active', true)->orderBy('degree_level')->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        return view('admin.evaluations.create', compact('programs', 'units'));
    }

    public function store(Request $request)
    {
        if (!$this->canCreate()) {
            abort(403, 'Anda tidak berhak membuat Evaluasi Diri.');
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'academic_year' => 'nullable|max:10',
            'semester' => 'nullable|in:Ganjil,Genap,Tahunan',
        ]);

        $user = auth()->user();

        if ($user->hasRole('prodi')) {
            $type = AcademicProgram::class; $id = $user->academic_program_id;
            $label = $user->academicProgram->name ?? '';
        } elseif ($user->hasRole('unit')) {
            $type = Unit::class; $id = $user->unit_id;
            $label = $user->unit->name ?? '';
        } else {
            $request->validate(['evaluable_type' => 'required|in:prodi,unit', 'evaluable_id' => 'required|integer']);
            $type = $request->evaluable_type === 'prodi' ? AcademicProgram::class : Unit::class;
            $targetModel = $request->evaluable_type === 'prodi'
                ? AcademicProgram::findOrFail($request->evaluable_id)
                : Unit::findOrFail($request->evaluable_id);
            $id = $targetModel->id;
            $label = $targetModel->name;
        }

        $evaluation = Evaluation::create([
            'name' => $request->name ?: ('Evaluasi Diri ' . $label . ' ' . ($request->academic_year ?? '')),
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'evaluable_type' => $type,
            'evaluable_id' => $id,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.evaluations.edit', $evaluation)
            ->with('success', 'Evaluasi Diri dibuat. Silakan isi indikator & unggah bukti.');
    }

    public function show(Evaluation $evaluation)
    {
        $this->authorizeView($evaluation);
        return $this->render($evaluation);
    }

    public function edit(Evaluation $evaluation)
    {
        $this->authorizeFillOrView($evaluation);
        return $this->render($evaluation);
    }

    private function render(Evaluation $evaluation)
    {
        $evaluation->load(['items.attachments', 'attachments', 'evaluable', 'creator']);
        $standards = QualityStandard::where('is_active', true)->orderBy('kode_standar')->get();
        $canFill = $this->canFill($evaluation);
        $canVerify = $this->canVerify();

        // Profil risiko milik pemilik ED (prodi/unit) sebagai pengingat statis per indikator.
        $ownerRisks = RiskRegister::query()
            ->when(
                $evaluation->evaluable_type === AcademicProgram::class,
                fn ($q) => $q->where('academic_program_id', $evaluation->evaluable_id)
            )
            ->when(
                $evaluation->evaluable_type === Unit::class,
                fn ($q) => $q->where('unit_id', $evaluation->evaluable_id)
            )
            ->when(
                !in_array($evaluation->evaluable_type, [AcademicProgram::class, Unit::class]),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->get();

        return view('admin.evaluations.edit', compact('evaluation', 'standards', 'canFill', 'canVerify', 'ownerRisks'));
    }

    /**
     * Tambah indikator baru pada ED.
     */
    public function storeItem(Request $request, Evaluation $evaluation)
    {
        $this->authorizeFill($evaluation);
        $request->validate([
            'indicator' => 'required|string|max:255',
            'quality_standard_id' => 'nullable|exists:quality_standards,id',
        ]);

$standard = $request->quality_standard_id
            ? QualityStandard::find($request->quality_standard_id)
            : null;

        $evaluation->items()->create([
            'indicator' => $request->indicator,
            'quality_standard_id' => $request->quality_standard_id,
            'criteria' => $standard
                ? ($standard->description ?: $standard->indicatorSummary() ?: $standard->pernyataan_standar)
                : null,
        ]);

return back()->with('success', 'Indikator ditambahkan.');
    }

    /**
     * Generate indikator ED dari Daftar Tilik (master instrumen SPMI).
     * Menarik seluruh checklist item aktif dari standar aktif.
     */
    public function generateFromChecklist(Request $request, Evaluation $evaluation)
    {
        $this->authorizeFill($evaluation);
        $request->validate([
            'standard_id' => 'nullable|exists:quality_standards,id',
        ]);

        $items = ChecklistItem::query()
            ->where('is_active', true)
            ->when($request->filled('standard_id'), fn ($q) => $q->where('quality_standard_id', $request->standard_id))
            ->whereHas('standard', fn ($q) => $q->where('is_active', true))
            ->orderBy('quality_standard_id')->orderBy('sort_order')->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Tidak ada butir Daftar Tilik aktif untuk di-generate.');
        }

        $existingIndicatorKeys = $evaluation->items()
            ->whereNotNull('checklist_item_id')
            ->get()->pluck('checklist_item_id')->all();

        $added = 0;
        foreach ($items as $item) {
            if (in_array($item->id, $existingIndicatorKeys)) {
                continue;
            }
            $evaluation->items()->create([
                'quality_standard_id'  => $item->quality_standard_id,
                'criteria'             => $item->standard?->pernyataan_standar ?: $item->standard?->name,
                'indicator'            => ($item->code ? "[{$item->code}] " : '') . $item->indicator,
                'checklist_item_id'    => $item->id,
            ]);
            $existingIndicatorKeys[] = $item->id;
            $added++;
        }

        return back()->with('success', "ED berhasil di-generate dari Daftar Tilik SPMI: {$added} indikator ditambahkan.");
    }

    /**
     * Simpan seluruh indikator (skor, narasi, akar, dampak, mitigasi, tindak lanjut, target).
     */
public function updateItems(Request $request, Evaluation $evaluation)
    {
        $this->authorizeFill($evaluation);

        $request->validate([
            'items'             => 'required|array',
            'items.*.narasi'    => 'required|string',
            'items.*.self_assessment' => 'required|in:Tercapai,Belum Tercapai',
        ], [
            'items.required'      => 'Belum ada indikator untuk disimpan.',
            'items.*.narasi.required' => 'Deskripsi Capaian & Analisis wajib diisi untuk setiap indikator.',
            'items.*.self_assessment.required' => 'Penilaian Mandiri (Tercapai/Belum Tercapai) wajib dipilih untuk setiap indikator.',
            'items.*.self_assessment.in' => 'Penilaian Mandiri hanya boleh bernilai Tercapai atau Belum Tercapai.',
        ]);

        foreach ($request->input('items', []) as $itemId => $data) {
            $item = EvaluationItem::where('evaluation_id', $evaluation->id)->find($itemId);
            if (!$item) {
                continue;
            }
$item->update([
                'criteria' => $data['criteria'] ?? $item->criteria,
                'indicator' => $data['indicator'] ?? $item->indicator,
                'score' => $data['score'] !== '' ? $data['score'] : null,
                'self_assessment' => $data['self_assessment'] ?? null,
                'narasi' => $data['narasi'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        }

        return back()->with('success', 'Evaluasi Diri berhasil disimpan.');
    }

    public function destroyItem(Request $request, Evaluation $evaluation, EvaluationItem $item)
    {
        $this->authorizeFill($evaluation);
        if ($item->evaluation_id !== $evaluation->id) {
            abort(403);
        }
        $item->attachments()->delete();
        $item->delete();

        return back()->with('success', 'Indikator dihapus.');
    }

    /**
     * Lampirkan bukti (upload file / link Drive) ke evaluasi atau indikator.
     */
    public function storeAttachment(Request $request, Evaluation $evaluation)
    {
        $this->authorizeFill($evaluation);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'item_id' => 'nullable|exists:evaluation_items,id',
        ]);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('evaluation_bukti', 'public');
            $fileName = $file->getClientOriginalName();
        }

        $attachable = $request->filled('item_id')
            ? EvaluationItem::where('evaluation_id', $evaluation->id)->findOrFail($request->item_id)
            : $evaluation;

        $attachable->attachments()->create([
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'link' => $request->link,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Bukti berhasil dilampirkan.');
    }

    public function destroyAttachment(Evaluation $evaluation, EvaluationAttachment $attachment)
    {
        $this->authorizeFill($evaluation);
        $this->deleteAttachment($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Bukti dihapus.');
    }

    /**
     * Lengkapi/kunci ED dan serahkan untuk peninjauan.
     */
    public function submit(Evaluation $evaluation)
    {
        $this->authorizeFill($evaluation);
        $evaluation->update(['status' => 'submitted']);

        return back()->with('success', 'Evaluasi Diri diserahkan untuk peninjauan.');
    }

    public function verify(Evaluation $evaluation, Request $request)
    {
        if (!$this->canVerify()) {
            abort(403);
        }
        $this->authorizeView($evaluation);

        $evaluation->update(['status' => 'verified', 'conclusion' => $request->conclusion]);
        return back()->with('success', 'Evaluasi Diri diverifikasi.');
    }

    public function destroy(Evaluation $evaluation)
    {
        $canDelete = $this->canManage($evaluation)
            || ($this->isOwner($evaluation) && $evaluation->status === 'draft');
        if (!$canDelete) {
            abort(403);
        }

        $evaluation->attachments()->delete();
        $evaluation->items()->each(fn($it) => $it->attachments()->delete());
        $evaluation->delete();

        return redirect()->route('admin.evaluations.index')->with('success', 'Evaluasi Diri dihapus.');
    }

    // ---------- Helpers akses ----------

private function canCreate(): bool
    {
        // SPMI bersifat monitoring (read-only); pembuatan ED oleh prodi/unit & administrator
        return auth()->user()->hasAnyRole(['administrator', 'prodi', 'unit']);
    }

    private function isOwner(Evaluation $evaluation): bool
    {
        $u = auth()->user();
        return ($u->hasRole('prodi') && $evaluation->evaluable_type === AcademicProgram::class && $evaluation->evaluable_id === $u->academic_program_id)
            || ($u->hasRole('unit') && $evaluation->evaluable_type === Unit::class && $evaluation->evaluable_id === $u->unit_id);
    }

    private function canManage(Evaluation $evaluation): bool
    {
        return auth()->user()->hasRole('administrator');
    }

    private function canFill(Evaluation $evaluation): bool
    {
        return $this->canManage($evaluation) || $this->isOwner($evaluation);
    }

    private function canVerify(): bool
    {
        return auth()->user()->hasAnyRole(['spmi', 'auditor', 'administrator']);
    }

    private function authorizeFill(Evaluation $evaluation): void
    {
        if (!$this->canFill($evaluation)) {
            abort(403, 'Anda tidak berhak mengubah Evaluasi Diri ini.');
        }
    }

    private function authorizeView(Evaluation $evaluation): void
    {
        $u = auth()->user();
        if ($u->hasAnyRole(['administrator', 'spmi', 'auditor', 'pimpinan']) || $this->isOwner($evaluation)) {
            return;
        }
        abort(403, 'Anda tidak memiliki akses ke evaluasi ini.');
    }

    private function authorizeCreate(): void
    {
        if (!$this->canCreate()) {
            abort(403);
        }
    }

    private function authorizeFillOrView(Evaluation $evaluation): void
    {
        $u = auth()->user();
        if ($u->hasAnyRole(['administrator', 'spmi', 'auditor', 'pimpinan']) || $this->isOwner($evaluation)) {
            return;
        }
        abort(403, 'Anda tidak memiliki akses ke evaluasi ini.');
    }

    private function deleteAttachment(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
