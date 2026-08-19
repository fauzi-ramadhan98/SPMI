<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditAssignment;
use App\Models\AuditFinding;
use App\Models\FindingAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class FindingAttachmentController extends Controller
{
    /**
     * Unggah bukti perbaikan pada temuan (RTL). Auditee = prodi/unit pemilik; spmi/auditor boleh lihat.
     */
    public function store(Request $request, AuditFinding $finding)
    {
        $this->authorizeFinding($finding);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'link' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ]);

        $filePath = null;
        $fileName = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('finding_bukti', 'public');
            $fileName = $file->getClientOriginalName();
        }

        $finding->attachments()->create([
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'link' => $request->link,
            'uploaded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Bukti perbaikan berhasil diunggah.');
    }

    public function destroy(Request $request, AuditFinding $finding, FindingAttachment $attachment)
    {
        $this->authorizeFinding($finding);

        if ($attachment->file_path && Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();

        return back()->with('success', 'Bukti perbaikan dihapus.');
    }

    /**
     * SPMI/auditor dapat mengelola; prodi/unit hanya untuk penugasan miliknya.
     */
    private function authorizeFinding(AuditFinding $finding): void
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['spmi', 'auditor', 'administrator', 'pimpinan'])) {
            return;
        }

        $assignment = $finding->assignment;
        $isOwner = ($user->hasRole('prodi') && $assignment->academic_program_id === $user->academic_program_id)
            || ($user->hasRole('unit') && $assignment->unit_id === $user->unit_id);

        if (!$isOwner) {
            abort(403, 'Anda tidak memiliki akses ke temuan ini.');
        }
    }
}