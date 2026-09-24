<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\AcademicProgram;
use App\Models\Unit;
use App\Models\AuditCycle;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Modul aktif (dokumen mutu, surat tugas, rtm). Admin/SPMI kelola semua.
        $module = $request->get('module', 'dokumen_mutu');
        $validModules = ['dokumen_mutu', 'surat_tugas', 'rtm'];
        if (!in_array($module, $validModules)) {
            $module = 'dokumen_mutu';
        }

        $query = Document::where('module', $module);

        // Dokumen mutu: filter "Tahun Terbit" (tahun akademik). Modul lain (surat tugas/rtm): siklus AMI.
        if ($module === 'dokumen_mutu') {
            if ($request->filled('academic_year')) {
                $query->where('academic_year', $request->get('academic_year'));
            }
        } elseif ($request->get('cycle_id')) {
            $query->where('audit_cycle_id', $request->get('cycle_id'));
        }

        if ($request->get('category_id')) {
            $query->where('document_category_id', $request->get('category_id'));
        }

        if ($user->hasRole('prodi')) {
            $query->where(fn($q) => $q->where('academic_program_id', $user->academic_program_id)->orWhere('uploaded_by', $user->id));
        } elseif ($user->hasRole('unit')) {
            $query->where(fn($q) => $q->where('unit_id', $user->unit_id)->orWhere('uploaded_by', $user->id));
        }

        $documents = $query->with(['category', 'cycle', 'uploader'])->latest()->paginate(10);

        $categories = DocumentCategory::where('module', $module)->where('is_active', true)->orderBy('name')->get();
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();
        $terbitYears = Document::where('module', 'dokumen_mutu')
            ->whereNotNull('academic_year')
            ->where('academic_year', '!=', '')
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('admin.documents.index', compact('documents', 'module', 'categories', 'cycles', 'terbitYears'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $module = $request->get('module', 'dokumen_mutu');

        $programs = AcademicProgram::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $categories = DocumentCategory::where('module', $module)->where('is_active', true)->orderBy('name')->get();
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();

        // Prodi/Unit hanya boleh mengatribusikan ke miliknya sendiri
        if ($user->hasRole('prodi')) {
            $programs = $programs->where('id', $user->academic_program_id);
        } elseif ($user->hasRole('unit')) {
            $units = $units->where('id', $user->unit_id);
        }

        return view('admin.documents.create', compact('module', 'programs', 'units', 'categories', 'cycles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255|unique:documents,code',
            'title' => 'required|string|max:255',
            'module' => 'required|in:dokumen_mutu,surat_tugas,rtm',
            'document_category_id' => 'required|exists:document_categories,id',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240', // Max 10MB
            'is_public' => 'boolean',
            'version' => 'nullable|integer',
            // Status otomatis 'draft', tidak perlu input user
        ]);

        $file = $request->file('file');

        // Simpan di private disk
        $path = $file->store('documents', 'local');

        $document = Document::create([
            'code' => $request->code,
            'version' => $request->version ?? 0,
            'status' => 'draft', // Selalu draft saat pertama dibuat
            'title' => $request->title,
            'document_type' => $this->resolveDocumentType($request->document_category_id),
            'module' => $request->module,
            'document_category_id' => $request->document_category_id,
            'academic_year' => $request->academic_year,
            'semester' => $request->semester,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'is_public' => $request->has('is_public'),
            'description' => $request->description,
            'uploaded_by' => Auth::id(),
            'academic_program_id' => $request->academic_program_id,
            'unit_id' => $request->unit_id,
            'audit_cycle_id' => $request->audit_cycle_id,
            'doc_date' => $request->doc_date,
        ]);

        // Dokumen baru selalu draft, tidak perlu de-aktifkan revisi lain

        return redirect()->route('admin.documents.index', ['module' => $request->module])
            ->with('success', 'Dokumen berhasil diunggah.');
    }

    public function edit(Document $document)
    {
        $this->authorizeDocumentAccess($document, allowOwner: true);

        $user = Auth::user();
        $module = $document->module ?: 'dokumen_mutu';
        $programs = AcademicProgram::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $categories = DocumentCategory::where('module', $module)->where('is_active', true)->orderBy('name')->get();
        $cycles = AuditCycle::orderBy('created_at', 'desc')->get();

        if ($user->hasRole('prodi')) {
            $programs = $programs->where('id', $user->academic_program_id);
        } elseif ($user->hasRole('unit')) {
            $units = $units->where('id', $user->unit_id);
        }

        // Riwayat perubahan dokumen ini
        $activityLogs = \App\Models\ActivityLog::where('model_type', Document::class)
            ->where('model_id', $document->id)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.documents.edit', compact('document', 'module', 'programs', 'units', 'categories', 'cycles', 'activityLogs'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorizeDocumentAccess($document, allowOwner: true);

        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:documents,code,' . $document->id,
            'document_category_id' => 'required|exists:document_categories,id',
            'is_public' => 'boolean',
            'version' => 'nullable|integer',
            // status tidak dapat diubah pada edit, jadi tidak divalidasi di sini
        ]);

        $data = $request->except(['file']);
        $data['version'] = $request->version;
        $data['is_public'] = $request->has('is_public');
        $data['document_type'] = $this->resolveDocumentType($request->document_category_id);

        // Jika dokumen sedang aktif dan di‑edit, kembalikan ke draft agar perlu persetujuan ulang.
        if ($document->status === 'aktif') {
            $data['status'] = 'draft';
        }

        if ($request->hasFile('file')) {
            $request->validate([
                'file' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240'
            ]);

            // Hapus file lama
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            $file = $request->file('file');
            $data['file_path'] = $file->store('documents', 'local');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        $document->update($data);

        $msg = 'Dokumen berhasil diperbarui.';
        if (isset($data['status']) && $data['status'] === 'draft') {
            $msg .= ' Status dikembalikan ke Draft karena ada perubahan pada dokumen aktif.';
        }

        return redirect()->route('admin.documents.index', ['module' => $document->module])
            ->with('success', $msg);
    }

    /**
     * Approve dokumen (pimpinan). Sets status to "aktif".
     */
    public function approve(Document $document)
    {
        $this->authorizeDocumentAccess($document, allowOwner: true);
        // Set this document to aktif and downgrade other revisions with same code
        $document->update(['status' => 'aktif']);
        Document::where('code', $document->code)
            ->where('id', '<>', $document->id)
            ->update(['status' => 'draft']);
        return redirect()->back()->with('success', 'Dokumen berhasil disetujui dan aktif.');
    }

    public function download(Document $document)
    {
        // Prodi/Unit hanya dapat mengunduh dokumen miliknya
        $this->authorizeDocumentAccess($document, allowOwner: true);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function destroy(Document $document)
    {
        $this->authorizeDocumentAccess($document, allowOwner: true);

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('admin.documents.index', ['module' => $document->module])
            ->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Turunkan nilai document_type (enum lama) secara otomatis dari kategori baku,
     * agar badge lama (dashboard/publik) tetap bermakna.
     */
    private function resolveDocumentType(?int $categoryId): string
    {
        if (!$categoryId) {
            return 'Lainnya';
        }

        $category = DocumentCategory::find($categoryId);
        if (!$category) {
            return 'Lainnya';
        }

        return match ($category->code) {
            'KEBIJAKAN' => 'Kebijakan',
            'MANUAL'    => 'Manual',
            'STANDAR'   => 'SPMI',
            'FORM-SOP'  => 'Formulir',
            default     => 'Lainnya',
        };
    }

    /**
     * Administrator/SPMI dapat mengelola semua dokumen; prodi/unit hanya miliknya.
     */
    private function authorizeDocumentAccess(Document $document, bool $allowOwner = true): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['administrator', 'spmi', 'auditor', 'pimpinan'])) {
            return;
        }

        $isOwner = $document->uploaded_by === $user->id
            || ($user->hasRole('prodi') && $document->academic_program_id === $user->academic_program_id)
            || ($user->hasRole('unit') && $document->unit_id === $user->unit_id);

        if (!$isOwner) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }
    }
}
