<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QualityStandard;
use App\Models\StandardDecree;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;


class StandardDecreeController extends Controller
{
    /**
     * Daftar SK Penetapan Standar (Modul Penetapan P1).
     * SPMI kelola; pimpinan koreksi, tandatangani (TTD otomatis) & tetapkan.
     */
    public function index()
    {
        // P1 — hanya SK Penetapan; SK Perubahan (hasil revisi) tampil di P5.2
        $decrees = StandardDecree::where('jenis', 'standar')
            ->where('kategori', 'penetapan')
            ->withCount(['standards', 'documents'])
            ->with(['issuedBy', 'preparedBy', 'cycle'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.standard_decrees.index', [
            'decrees'      => $decrees,
            'kategoriMode' => 'penetapan',
        ]);
    }

    /**
     * Daftar SK Penugasan Auditor (Modul Pengendalian P3).
     */
    public function auditorIndex()
    {
        $decrees = StandardDecree::where('jenis', 'auditor')
            ->withCount('standards')->with(['issuedBy', 'preparedBy', 'cycle'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.standard_decrees.auditor_index', compact('decrees'));
    }

    /**
     * P5.2 — Daftar SK Perubahan Standar (hasil revisi; kategori = 'perubahan').
     * Terpisah dari SK Penetapan awal (P1) agar alur revisi terlacak di P5.
     */
    public function perubahanIndex()
    {
        // Tanpa authorizeSpmi: route berada di grup spmi|pimpinan|administrator —
        // Pimpinan perlu membuka daftar ini untuk menandatangani SK Perubahan.
        $decrees = StandardDecree::where('jenis', 'standar')
            ->where('kategori', 'perubahan')
            ->withCount(['standards', 'documents'])
            ->with(['issuedBy', 'preparedBy', 'cycle'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.standard_decrees.index', [
            'decrees'      => $decrees,
            'kategoriMode' => 'perubahan',
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeSpmi();

        // P5.2 — form SK Perubahan dibuka dengan ?kategori=perubahan dari halaman Revisi
        $kategori = $request->query('kategori') === 'perubahan' ? 'perubahan' : 'penetapan';

        // Hanya tampilkan Dokumen Mutu dengan status Draft yang belum terhubung ke SK lain
        $draftDocuments = \App\Models\Document::where('module', 'dokumen_mutu')
            ->where('status', 'draft')
            ->whereNull('standard_decree_id')
            ->with('category')
            ->orderBy('code')
            ->get();

        // P5.2 — pilihan standar yang relevan: SK Perubahan hanya mengikat standar berstatus draft_revisi
        $standards = \App\Models\QualityStandard::when($kategori === 'perubahan', function ($q) {
                $q->where('revisi_status', 'draft_revisi');
            })
            ->orderBy('kode_standar')
            ->get();

        return view('admin.standard_decrees.create', compact('draftDocuments', 'standards', 'kategori'));
    }

    public function store(Request $request)
    {
        $this->authorizeSpmi();

        $validated = $this->validateDecree($request);

        $decree = StandardDecree::create([
            'sk_no'          => $validated['sk_no'],
            'judul'          => $validated['judul'],
            'kategori'       => $validated['kategori'] ?? 'penetapan',
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'menimbang'      => $validated['menimbang'] ?? null,
            'mengingat'      => $validated['mengingat'] ?? null,
            'memutuskan'     => $validated['memutuskan'] ?? null,
            'nama_standar'   => $validated['nama_standar'] ?? null,
            'lokasi'         => $validated['lokasi'] ?? 'Bandung',
            'tanggal_sk'     => $validated['tanggal_sk'] ?? null,
            'kop_path'       => 'images/kopstmik.jpg',
            'signature_path' => 'images/TTD-KETUA.jpg',
            'status'         => 'menunggu_persetujuan',
            'prepared_by'    => auth()->id(),
        ]);

        // Attach existing QualityStandards (legacy support)
        $this->attachStandards($decree, $validated['standard_ids'] ?? []);

        // Link selected Documents to this decree
        $documentIds = $validated['document_ids'] ?? [];
        if (!empty($documentIds)) {
            \App\Models\Document::whereIn('id', $documentIds)
                ->whereNull('standard_decree_id')
                ->update(['standard_decree_id' => $decree->id]);
        }

        $this->handleFileUpload($request, $decree, $validated['file_sk_final'] ?? null);

        $docCount = count($documentIds);
        $isPerubahan = $decree->kategori === 'perubahan';
        $label = $isPerubahan ? 'SK Perubahan' : 'SK Penetapan';
        $msg = $label . ' "' . $decree->sk_no . '" berhasil dibuat. Menunggu persetujuan Pimpinan.';
        if ($docCount > 0) {
            $msg .= ' Terhubung ke ' . $docCount . ' Dokumen Mutu.';
        }

        return redirect()->route($isPerubahan ? 'admin.standard-decrees.perubahan' : 'admin.standard-decrees.index')
            ->with('success', $msg);
    }

    public function edit(StandardDecree $decree)
    {
        $this->authorizeSpmi();
        $this->abortIfDitetapkan($decree);

        // Load draft documents for selection (draft + already linked to this decree)
        $draftDocuments = \App\Models\Document::where('module', 'dokumen_mutu')
            ->where(function ($q) use ($decree) {
                $q->where(function ($q2) use ($decree) {
                    $q2->where('status', 'draft')->whereNull('standard_decree_id');
                })->orWhere('standard_decree_id', $decree->id);
            })
            ->with('category')
            ->orderBy('code')
            ->get();

        // Load standards too — SK Perubahan (P5.2) hanya boleh mengikat standar draft_revisi
        $standards = QualityStandard::with('document')
            ->when($decree->kategori === 'perubahan', fn ($q) => $q->where('revisi_status', 'draft_revisi'))
            ->orderBy('kode_standar')
            ->get();
        $selected = $decree->standards->pluck('id')->all();
        $selectedDocIds = $decree->documents->pluck('id')->all();

        return view('admin.standard_decrees.edit', compact('decree', 'standards', 'selected', 'draftDocuments', 'selectedDocIds'));
    }

    public function update(Request $request, StandardDecree $decree)
    {
        $this->authorizeSpmi();
        $this->abortIfDitetapkan($decree);

        $validated = $this->validateDecree($request);

        // If SK was ditolak (rejected), resubmit to menunggu_persetujuan and clear reject_reason
        $wasDitolak = $decree->isDitolak();
        $newStatus = $wasDitolak ? 'menunggu_persetujuan' : $decree->status;

        $decree->update([
            'sk_no'          => $validated['sk_no'],
            'judul'          => $validated['judul'],
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'menimbang'      => $validated['menimbang'] ?? null,
            'mengingat'      => $validated['mengingat'] ?? null,
            'memutuskan'     => $validated['memutuskan'] ?? null,
            'nama_standar'   => $validated['nama_standar'] ?? null,
            'lokasi'         => $validated['lokasi'] ?? 'Bandung',
            'tanggal_sk'     => $validated['tanggal_sk'] ?? null,
            'status'         => $newStatus,
            'reject_reason'  => $wasDitolak ? null : $decree->reject_reason,
        ]);

        // Attach standards (legacy)
        $this->attachStandards($decree, $validated['standard_ids'] ?? [], replace: true);

        // Update document links
        $documentIds = $validated['document_ids'] ?? [];
        // Detach documents no longer selected
        \App\Models\Document::where('standard_decree_id', $decree->id)
            ->whereNotIn('id', $documentIds)
            ->update(['standard_decree_id' => null]);
        // Attach newly selected documents
        if (!empty($documentIds)) {
            \App\Models\Document::whereIn('id', $documentIds)
                ->whereNull('standard_decree_id')
                ->update(['standard_decree_id' => $decree->id]);
        }

        $this->handleFileUpload($request, $decree, $validated['file_sk_final'] ?? null);

        $msg = $wasDitolak
            ? ($decree->kategori === 'perubahan' ? 'SK Perubahan "' : 'SK Penetapan "') . $decree->sk_no . '" berhasil diperbarui dan diajukan ulang.'
            : 'Draf SK "' . $decree->sk_no . '" berhasil diperbarui.';

        return redirect()->route($decree->kategori === 'perubahan' ? 'admin.standard-decrees.perubahan' : 'admin.standard-decrees.index')
            ->with('success', $msg);
    }

    public function destroy(StandardDecree $decree)
    {
        $this->authorizeSpmi();
        $this->abortIfDitetapkan($decree);

        // Detach documents before deleting
        $decree->documents()->update(['standard_decree_id' => null]);
        $this->attachStandards($decree, [], replace: true);
        $this->deleteFile($decree);
        $isPerubahan = $decree->kategori === 'perubahan';
        $decree->delete();

        return redirect()->route($isPerubahan ? 'admin.standard-decrees.perubahan' : 'admin.standard-decrees.index')
            ->with('success', 'Draf SK Penetapan berhasil dihapus.');
    }

    public function review(StandardDecree $decree)
    {
        $this->authorizePimpinan();

        if ($decree->isDitetapkan()) {
            return redirect()->route('admin.standard-decrees.index')
                ->with('error', 'SK Penetapan ini sudah ditetapkan dan tidak dapat dikoreksi.');
        }

        if ($decree->isDitolak()) {
            return redirect()->route('admin.standard-decrees.index')
                ->with('error', 'SK Penetapan ini sudah ditolak. Hanya SPMI yang dapat mengedit dan mengajukan ulang.');
        }

        $decree->load(['standards', 'cycle.assignments']);

        return view('admin.standard_decrees.review', compact('decree'));
    }

    public function reviewUpdate(Request $request, StandardDecree $decree)
    {
        $this->authorizePimpinan();
        $this->abortIfDitetapkan($decree);

        if ($decree->isDitolak()) {
            return back()->with('error', 'SK Penetapan ini sudah ditolak. Hanya SPMI yang dapat mengedit dan mengajukan ulang.');
        }

        $validated = $this->validateDecree($request);

        $decree->update([
            'sk_no'          => $validated['sk_no'],
            'judul'          => $validated['judul'],
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'menimbang'      => $validated['menimbang'] ?? null,
            'mengingat'      => $validated['mengingat'] ?? null,
            'memutuskan'     => $validated['memutuskan'] ?? null,
            'nama_standar'   => $validated['nama_standar'] ?? null,
            'lokasi'         => $validated['lokasi'] ?? 'Bandung',
            'tanggal_sk'     => $validated['tanggal_sk'] ?? null,
        ]);

        return redirect()->route('admin.standard-decrees.review', $decree)
            ->with('success', 'Koreksi isi SK "' . $decree->sk_no . '" berhasil disimpan.');
    }

    public function verify(StandardDecree $decree)
    {
        $this->authorizePimpinan();

        if ($decree->isDitetapkan()) {
            return back()->with('error', 'SK Penetapan ini sudah ditetapkan dan tidak dapat ditandatangani ulang.');
        }

        if ($decree->jenis === 'auditor' && ! $decree->audit_cycle_id) {
            return back()->with('error', 'Siklus Auditor wajib diisi sebelum SK auditor dapat ditetapkan.');
        }

        // Pastikan minimal ada Dokumen Mutu atau Standar yang terhubung
        $hasDocuments = $decree->documents()->count() > 0;
        $hasStandards = $decree->standards()->count() > 0;
        if (!$hasDocuments && !$hasStandards) {
            return back()->with('error', 'Minimal harus ada Dokumen Mutu atau Standar yang dipilih sebelum SK dapat ditetapkan.');
        }

        // 1. Set SK status to ditetapkan
        $decree->update([
            'status'         => 'ditetapkan',
            'signature_path' => $decree->signature_path ?: 'images/TTD-KETUA.jpg',
            'kop_path'       => $decree->kop_path ?: 'images/kopstmik.jpg',
            'issued_by'      => auth()->id(),
            'issued_at'      => now(),
        ]);

        // 2. Activate all Documents linked to this SK
        $decree->documents()->update(['status' => 'aktif']);

        // 3. Activate all QualityStandards linked to this SK (legacy support)
        $decree->standards()->update(['is_active' => true]);

        // 3b. Ratifikasi revisi (P5): standar yang terikat SK ini kembali 'aktif'
        $decree->standards()->whereNotNull('revisi_status')->update(['revisi_status' => 'aktif']);

        $isPerubahan = $decree->kategori === 'perubahan';
        $label = $isPerubahan ? 'SK Perubahan' : 'SK Penetapan';
        $extra = $isPerubahan
            ? ' Status revisi standar terkait kembali Aktif.'
            : ' Dokumen Mutu terkait kini Aktif.';

        return redirect()->route($isPerubahan ? 'admin.standard-decrees.perubahan' : 'admin.standard-decrees.index')
            ->with('success', $label . ' "' . $decree->sk_no . '" berhasil ditetapkan.' . $extra);
    }

    /**
     * Tolak/Dikembalikan SK oleh Pimpinan.
     * Status berubah menjadi 'ditolak' + catatan penolakan wajib diisi.
     */
    public function reject(Request $request, StandardDecree $decree)
    {
        $this->authorizePimpinan();

        if ($decree->isDitetapkan()) {
            return back()->with('error', 'SK Penetapan ini sudah ditetapkan dan tidak dapat ditolak.');
        }

        if ($decree->isDitolak()) {
            return back()->with('error', 'SK Penetapan ini sudah ditolak sebelumnya.');
        }

        $request->validate([
            'reject_reason' => 'required|string|max:1000',
        ], [
            'reject_reason.required' => 'Alasan penolakan wajib diisi.',
            'reject_reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $decree->update([
            'status'        => 'ditolak',
            'reject_reason' => $request->input('reject_reason'),
        ]);

        return redirect()->route('admin.standard-decrees.index')
            ->with('success', 'SK Penetapan "' . $decree->sk_no . '" telah dikembalikan ke SPMI. SPMI dapat mengedit dan mengajukan ulang.');
    }

    public function pdf(StandardDecree $decree)
    {
        $decree->load(['cycle.assignments.academicProgram', 'cycle.assignments.unit', 'cycle.assignments.auditor']);

        $kopPath = public_path($decree->kop_path ?: 'images/kopstmik.jpg');
        $ttdPath = public_path($decree->signature_path ?: 'images/TTD-KETUA.jpg');

        $kop = is_file($kopPath) ? $this->toDataUri($kopPath) : null;
        $ttd = is_file($ttdPath) ? $this->toDataUri($ttdPath) : null;

        $pdf = Pdf::loadView('admin.standard_decrees.pdf', compact('decree', 'kop', 'ttd'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_left' => 0,
                'margin_right' => 0,
            ]);

        $safeNo = str_replace(['/', '\\', ' '], '-', $decree->sk_no);

        return $pdf->stream('SK-' . $safeNo . '.pdf');
    }

    public function downloadFile(StandardDecree $decree)
    {
        if (!$decree->file_path || !Storage::disk('local')->exists($decree->file_path)) {
            abort(404, 'File SK tidak ditemukan.');
        }

        return Storage::disk('local')->download($decree->file_path, $decree->file_name);
    }

    public function uploadFile(Request $request, StandardDecree $decree)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ], [
            'file.required' => 'Pilih file SK terlebih dahulu.',
            'file.mimes'    => 'Format file SK harus PDF, Word, atau gambar.',
            'file.max'      => 'Ukuran file SK maksimal adalah 10 MB.',
        ]);

        $file = $request->file('file');
        $path = $file->store('standard_decrees', 'local');
        $ext  = strtolower($file->getClientOriginalExtension());
        $type = in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image'
            : (in_array($ext, ['pdf']) ? 'pdf' : 'document');

        $decree->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $type,
        ]);

        return redirect()->route($decree->kategori === 'perubahan' ? 'admin.standard-decrees.perubahan' : 'admin.standard-decrees.index')
            ->with('success', 'File SK berhasil diunggah.');
    }

    private function validateDecree(Request $request): array
    {
        // SK Perubahan (P5.2): standar wajib dipilih lebih dulu di halaman Revisi (P5.1).
        // SK Penetapan (P1): standar boleh dipilih bertahap setelah SK tersimpan (perilaku lama).
        $standardRules = $request->input('kategori') === 'perubahan'
            ? ['standard_ids' => 'required|array|min:1', 'standard_ids.*' => 'exists:quality_standards,id']
            : ['standard_ids' => 'nullable|array', 'standard_ids.*' => 'exists:quality_standards,id'];

        return $request->validate([
            'sk_no'          => 'required|string|max:100',
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'nullable|string',
            'menimbang'      => 'nullable|string',
            'mengingat'      => 'nullable|string',
            'memutuskan'     => 'nullable|string',
            'nama_standar'   => 'nullable|string',
            'lokasi'         => 'nullable|string|max:100',
            'tanggal_sk'     => 'nullable|date',
            'kategori'       => 'nullable|in:penetapan,perubahan',
            'document_ids'    => 'nullable|array',
            'document_ids.*'  => 'exists:documents,id',
            'file_sk_final'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ] + $standardRules);
    }

    private function attachStandards(StandardDecree $decree, array $selected, bool $replace = false): void
    {
        if ($replace) {
            QualityStandard::where('standard_decree_id', $decree->id)
                ->whereNotIn('id', $selected)
                ->update(['standard_decree_id' => null]);
        }

        if (!empty($selected)) {
            QualityStandard::whereIn('id', $selected)->update(['standard_decree_id' => $decree->id]);
        }
    }

    private function deleteFile(StandardDecree $decree): void
    {
        if ($decree->file_path && Storage::disk('local')->exists($decree->file_path)) {
            Storage::disk('local')->delete($decree->file_path);
        }
    }

    private function handleFileUpload(Request $request, StandardDecree $decree, $file): void
    {
        if (!$file instanceof UploadedFile) {
            return;
        }

        $path = $file->store('standard_decrees', 'local');
        $ext  = strtolower($file->getClientOriginalExtension());
        $type = in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image'
            : (in_array($ext, ['pdf']) ? 'pdf' : 'document');

        $decree->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'file_type' => $type,
        ]);
    }

    private function abortIfDitetapkan(StandardDecree $decree): void
    {
        if ($decree->isDitetapkan()) {
            abort(403, 'SK ini sudah ditetapkan dan tidak dapat diubah.');
        }
    }

    private function authorizeSpmi(): void
    {
        if (!Auth::user()->hasAnyRole(['administrator', 'spmi'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola SK.');
        }
    }

    private function authorizePimpinan(): void
    {
        if (!Auth::user()->hasAnyRole(['administrator', 'pimpinan'])) {
            abort(403, 'Anda tidak memiliki akses untuk meninjau SK.');
        }
    }

    private function toDataUri($path): string
    {
        if (!is_file($path)) return '';
        $data = file_get_contents($path);
        $type = pathinfo($path, PATHINFO_EXTENSION);
        // Basic mime type mapping
        $mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
        $mime = $mimes[$type] ?? 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
