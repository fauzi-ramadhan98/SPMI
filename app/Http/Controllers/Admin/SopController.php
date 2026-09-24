<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SopController extends Controller
{
    /**
     * Unit: Daftar SOP milik unit sendiri
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('unit')) {
            $unit = $user->unit;
            if (!$unit) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Anda tidak terdaftar di unit manapun.');
            }
            $sops = Sop::where('unit_id', $unit->id)
                ->with(['creator', 'reviewer'])
                ->orderByDesc('created_at')
                ->paginate(15);
        } elseif ($user->hasRole('spmi|administrator|super_admin')) {
            $sops = Sop::with(['unit', 'creator', 'reviewer'])
                ->orderByDesc('created_at')
                ->paginate(15);
        } else {
            abort(403);
        }

        return view('admin.sops.index', compact('sops'));
    }

    /**
     * Unit: Form buat SOP baru
     */
    public function create()
    {
        $this->authorizeUnit();
        $unit = Auth::user()->unit;

        if (!$unit) {
            return back()->with('error', 'Anda tidak terdaftar di unit manapun.');
        }

        return view('admin.sops.create', compact('unit'));
    }

    /**
     * Unit: Simpan draf SOP baru
     */
    public function store(Request $request)
    {
        $this->authorizeUnit();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'file'        => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'title.required'  => 'Judul SOP wajib diisi.',
            'file.required'   => 'File draf SOP wajib diunggah.',
            'file.mimes'      => 'Format file harus PDF, DOC, atau DOCX.',
            'file.max'        => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $request->file('file');
        $path = $file->store('sops', 'local');
        $ext  = strtolower($file->getClientOriginalExtension());
        $type = in_array($ext, ['doc', 'docx']) ? 'word' : 'pdf';

        $sop = Sop::create([
            'unit_id'      => Auth::user()->unit_id,
            'created_by'   => Auth::id(),
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'file_path'    => $path,
            'file_name'    => $file->getClientOriginalName(),
            'file_type'    => $type,
            'file_size'    => $file->getSize(),
            'status'       => 'pending',
        ]);

        return redirect()->route('admin.sops.index')
            ->with('success', 'SOP "' . $sop->title . '" berhasil diajukan dan menunggu review SPMI.');
    }

    /**
     * Detail SOP (semua role yang berhak)
     */
    public function show(Sop $sop)
    {
        $user = Auth::user();

        if ($user->hasRole('unit') && $sop->unit_id !== $user->unit_id) {
            abort(403);
        }

        $sop->load(['unit', 'creator', 'reviewer']);
        return view('admin.sops.show', compact('sop'));
    }

    /**
     * Unit: Upload ulang file saat revisi
     */
    public function uploadRevision(Request $request, Sop $sop)
    {
        $this->authorizeUnit();

        if ($sop->unit_id !== Auth::user()->unit_id) {
            abort(403);
        }

        if (!$sop->isRevisi()) {
            return back()->with('error', 'SOP ini tidak dalam status revisi.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'file.required' => 'File revisi wajib diunggah.',
            'file.mimes'    => 'Format file harus PDF, DOC, atau DOCX.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $request->file('file');
        $this->deleteFile($sop);

        $path = $file->store('sops', 'local');
        $ext  = strtolower($file->getClientOriginalExtension());
        $type = in_array($ext, ['doc', 'docx']) ? 'word' : 'pdf';

        $sop->update([
            'file_path'      => $path,
            'file_name'      => $file->getClientOriginalName(),
            'file_type'      => $type,
            'file_size'      => $file->getSize(),
            'status'         => 'pending',
            'review_notes'   => null,
            'reviewed_by'    => null,
            'reviewed_at'    => null,
        ]);

        return redirect()->route('admin.sops.index')
            ->with('success', 'File revisi SOP "' . $sop->title . '" berhasil diunggah. Status kembali ke Menunggu Review.');
    }

    /**
     * SPMI: Halaman review SOP (approve/reject)
     */
    public function review(Sop $sop)
    {
        $this->authorizeSpmi();

        if (!$sop->isPending()) {
            return redirect()->route('admin.sops.index')
                ->with('error', 'SOP ini tidak dalam status menunggu review.');
        }

        $sop->load(['unit', 'creator']);
        return view('admin.sops.review', compact('sop'));
    }

    /**
     * SPMI: Proses keputusan review
     */
    public function reviewStore(Request $request, Sop $sop)
    {
        $this->authorizeSpmi();

        if (!$sop->isPending()) {
            return back()->with('error', 'SOP ini tidak dalam status menunggu review.');
        }

        $validated = $request->validate([
            'action'       => 'required|in:approve,reject',
            'review_notes' => 'nullable|string|max:2000',
        ]);

        if ($validated['action'] === 'approve') {
            $sop->update([
                'status'       => 'approved',
                'reviewed_by'  => Auth::id(),
                'reviewed_at'  => now(),
                'review_notes' => $validated['review_notes'] ?? null,
            ]);

            return redirect()->route('admin.sops.index')
                ->with('success', 'SOP "' . $sop->title . '" berhasil disetujui.');
        } else {
            $request->validate([
                'review_notes' => 'required|string|max:2000',
            ], [
                'review_notes.required' => 'Catatan revisi wajib diisi saat menolak.',
            ]);

            $sop->update([
                'status'       => 'revisi',
                'reviewed_by'  => Auth::id(),
                'reviewed_at'  => now(),
                'review_notes' => $validated['review_notes'],
            ]);

            return redirect()->route('admin.sops.index')
                ->with('success', 'SOP "' . $sop->title . '" dikembalikan untuk revisi.');
        }
    }

    /**
     * Download file SOP
     */
    public function download(Sop $sop)
    {
        $user = Auth::user();

        if ($user->hasRole('unit') && $sop->unit_id !== $user->unit_id) {
            abort(403);
        }

        if (!$sop->file_path || !Storage::disk('local')->exists($sop->file_path)) {
            abort(404, 'File SOP tidak ditemukan.');
        }

        return Storage::disk('local')->download($sop->file_path, $sop->file_name);
    }

    /**
     * Preview file SOP (inline)
     */
    public function preview(Sop $sop)
    {
        $user = Auth::user();

        if ($user->hasRole('unit') && $sop->unit_id !== $user->unit_id) {
            abort(403);
        }

        if (!$sop->file_path || !Storage::disk('local')->exists($sop->file_path)) {
            abort(404, 'File SOP tidak ditemukan.');
        }

        return Storage::disk('local')->response($sop->file_path);
    }

    private function authorizeUnit(): void
    {
        if (!Auth::user()->hasRole('unit')) {
            abort(403, 'Hanya role Unit yang dapat mengelola SOP unit.');
        }
    }

    private function authorizeSpmi(): void
    {
        if (!Auth::user()->hasAnyRole(['spmi', 'administrator', 'super_admin'])) {
            abort(403, 'Hanya SPMI/Administrator yang dapat mereview SOP.');
        }
    }

    private function deleteFile(Sop $sop): void
    {
        if ($sop->file_path && Storage::disk('local')->exists($sop->file_path)) {
            Storage::disk('local')->delete($sop->file_path);
        }
    }
}