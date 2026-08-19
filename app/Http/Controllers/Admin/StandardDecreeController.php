<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QualityStandard;
use App\Models\StandardDecree;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StandardDecreeController extends Controller
{
    /**
     * Daftar SK Penetapan Standar (Modul Penetapan P1).
     * SPMI kelola; pimpinan koreksi, tandatangani (TTD otomatis) & tetapkan.
     */
    public function index()
    {
        $decrees = StandardDecree::where('jenis', 'standar')
            ->withCount('standards')->with(['issuedBy', 'preparedBy', 'cycle'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.standard_decrees.index', compact('decrees'));
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

    public function create()
    {
        $this->authorizeSpmi();
        $standards = QualityStandard::where('is_active', true)->orderBy('kode_standar')->get();

        return view('admin.standard_decrees.create', compact('standards'));
    }

    public function store(Request $request)
    {
        $this->authorizeSpmi();

        $validated = $this->validateDecree($request);

        $decree = StandardDecree::create([
            'sk_no'          => $validated['sk_no'],
            'judul'          => $validated['judul'],
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'menimbang'      => $validated['menimbang'] ?? null,
            'mengingat'      => $validated['mengingat'] ?? null,
            'memutuskan'     => $validated['memutuskan'] ?? null,
            'nama_standar'   => $validated['nama_standar'] ?? null,
            'lokasi'         => $validated['lokasi'] ?? 'Bandung',
            'tanggal_sk'     => $validated['tanggal_sk'] ?? null,
            'kop_path'       => 'images/kopstmik.jpg',
            'signature_path' => 'images/TTD-KETUA.jpg',
            'status'         => 'draft',
            'prepared_by'    => auth()->id(),
        ]);

        $this->attachStandards($decree, $validated['standard_ids'] ?? []);
        $this->handleFileUpload($request, $decree, $validated['file_sk_final'] ?? null);

        return redirect()->route('admin.standard-decrees.index')
            ->with('success', 'Draf SK Penetapan "' . $decree->sk_no . '" berhasil dibuat.');
    }

    public function edit(StandardDecree $decree)
    {
        $this->authorizeSpmi();
        $this->abortIfDitetapkan($decree);
        $standards = QualityStandard::where('is_active', true)->orderBy('kode_standar')->get();
        $selected = $decree->standards->pluck('id')->all();

        return view('admin.standard_decrees.edit', compact('decree', 'standards', 'selected'));
    }

    public function update(Request $request, StandardDecree $decree)
    {
        $this->authorizeSpmi();
        $this->abortIfDitetapkan($decree);

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

        $this->attachStandards($decree, $validated['standard_ids'] ?? [], replace: true);
        $this->handleFileUpload($request, $decree, $validated['file_sk_final'] ?? null);

        return redirect()->route('admin.standard-decrees.index')
            ->with('success', 'Draf SK Penetapan "' . $decree->sk_no . '" berhasil diperbarui.');
    }

    public function destroy(StandardDecree $decree)
    {
        $this->authorizeSpmi();
        $this->abortIfDitetapkan($decree);

        $this->attachStandards($decree, [], replace: true);
        $this->deleteFile($decree);
        $decree->delete();

        return redirect()->route('admin.standard-decrees.index')
            ->with('success', 'Draf SK Penetapan berhasil dihapus.');
    }

    /**
     * Pimpinan: halaman koreksi isi draf SK sebelum ditetapkan.
     */
    public function review(StandardDecree $decree)
    {
        $this->authorizePimpinan();

        if ($decree->isDitetapkan()) {
            return redirect()->route('admin.standard-decrees.index')
                ->with('error', 'SK Penetapan ini sudah ditetapkan dan tidak dapat dikoreksi.');
        }

        $decree->load(['standards', 'cycle.assignments']);

        return view('admin.standard_decrees.review', compact('decree'));
    }

    /**
     * Pimpinan: simpan hasil koreksi isi draf SK.
     */
    public function reviewUpdate(Request $request, StandardDecree $decree)
    {
        $this->authorizePimpinan();
        $this->abortIfDitetapkan($decree);

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

    /**
     * Pimpinan menandatangani & menetapkan SK.
     * TTD Ketua ditempel otomatis dari gambar TTD-KETUA.jpg (tanpa upload).
     */
    public function verify(StandardDecree $decree)
    {
        $this->authorizePimpinan();

        if ($decree->isDitetapkan()) {
            return back()->with('error', 'SK Penetapan ini sudah ditetapkan dan tidak dapat ditandatangani ulang.');
        }

        if (! $decree->audit_cycle_id) {
            return back()->with('error', 'Siklus Auditor wajib diisi sebelum SK dapat ditetapkan.');
        }

        if ($decree->standards()->count() === 0) {
            return back()->with('error', 'Standar Mutu wajib dipilih sebelum SK dapat ditetapkan.');
        }

        $decree->update([
            'status'         => 'ditetapkan',
            'signature_path' => $decree->signature_path ?: 'images/TTD-KETUA.jpg',
            'kop_path'       => $decree->kop_path ?: 'images/kopstmik.jpg',
            'issued_by'      => auth()->id(),
            'issued_at'      => now(),
        ]);

        return redirect()->route('admin.standard-decrees.index')
            ->with('success', 'SK Penetapan "' . $decree->sk_no . '" berhasil ditetapkan dan ditandatangani.');
    }

    /**
     * Generate & tampilkan PDF SK (kop + isi manual + lampiran daftar auditor + TTD).
     */
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

    /**
     * Unggah file SK (PDF/Word/Gambar). SPMI/Pimpinan/Administrator.
     * Yang sudah diunggah tetap bisa diganti; ditetapkan tetap terkunci dari delete/edit isi.
     */
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
        $type = in_array($ext, ['jpg', 'jpeg', 'png']) ? 'image' : (in_array($ext, ['doc', 'docx']) ? 'word' : 'pdf');

        $this->deleteFile($decree);

        $decree->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $type,
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('admin.standard-decrees.index')
            ->with('success', 'File SK "' . $decree->sk_no . '" berhasil diunggah.');
    }

    private function validateDecree(Request $request): array
    {
        return $request->validate([
            'sk_no'           => 'required|string|max:100',
            'judul'           => 'required|string|max:255',
            'deskripsi'       => 'nullable|string',
            'menimbang'       => 'nullable|string',
            'mengingat'       => 'nullable|string',
            'memutuskan'      => 'nullable|string',
            'nama_standar'    => 'nullable|string|max:500',
            'lokasi'          => 'nullable|string|max:100',
            'tanggal_sk'      => 'nullable|date',
            'standard_ids'    => 'nullable|array',
            'standard_ids.*'  => 'exists:quality_standards,id',
            'file_sk_final'   => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'sk_no.required'     => 'Nomor SK wajib diisi.',
            'judul.required'     => 'Judul SK wajib diisi.',
            'file_sk_final.mimes' => 'File SK Final harus berformat PDF.',
            'file_sk_final.max'   => 'Ukuran file SK Final maksimal 10 MB.',
        ]);
    }

    private function authorizeSpmi(): void
    {
        if (!auth()->user()->hasRole('spmi')) {
            abort(403, 'Hanya SPMI yang dapat mengelola draf SK Penetapan.');
        }
    }

    private function authorizePimpinan(): void
    {
        if (!auth()->user()->hasRole('pimpinan')) {
            abort(403, 'Hanya pimpinan yang dapat mengoreksi, menandatangani, dan menetapkan SK.');
        }
    }

    private function abortIfDitetapkan(StandardDecree $decree): void
    {
        if ($decree->isDitetapkan()) {
            abort(403, 'SK yang sudah ditetapkan tidak dapat diubah atau dihapus.');
        }
    }

    private function deleteFile(StandardDecree $decree): void
    {
        if ($decree->file_path && Storage::disk('local')->exists($decree->file_path)) {
            Storage::disk('local')->delete($decree->file_path);
        }
    }

    private function handleFileUpload(Request $request, StandardDecree $decree, ?UploadedFile $file = null): void
    {
        if (!$file) {
            return;
        }

        $this->deleteFile($decree);

        $path = $file->store('standard_decrees', 'local');

        $decree->update([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => 'pdf',
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Atur standar yang dicakup oleh SK (kolom standard_decree_id).
     * replace: lepas standar lama yang tidak terpilih, lalu tetapkan yang dipilih.
     */
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

    private function toDataUri(string $path): string
    {
        $type = mime_content_type($path);
        return 'data:' . $type . ';base64,' . base64_encode(file_get_contents($path));
    }
}