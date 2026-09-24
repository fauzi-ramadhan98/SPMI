<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\QualityStandard;
use App\Models\StandardVersion;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QualityStandardController extends Controller
{
    public function index()
    {
        $query = QualityStandard::with(['decree', 'document.category', 'applicabilities.academicProgram', 'applicabilities.unit', 'parent'])
            ->orderBy('kode_standar')->orderBy('created_at', 'desc');

        $user = auth()->user();
        if ($user && $user->hasAnyRole(['prodi', 'unit'])) {
            $query->forUser($user);
        }

        $standards = $query->paginate(20);

        return view('admin.quality_standards.index', compact('standards'));
    }

    public function create()
    {
        $programs = AcademicProgram::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        // Hanya tampilkan Dokumen Mutu yang sudah aktif (sudah di-SK-kan oleh pimpinan)
        $documents = \App\Models\Document::where('module', 'dokumen_mutu')
            ->where('status', 'aktif')
            ->with('decree')
            ->orderBy('code')
            ->get();

        return view('admin.quality_standards.create', compact('programs', 'units', 'documents'));
    }

    private function syncApplicabilitiesFromRequest(QualityStandard $standard, Request $request): void
    {
        $mode = $request->input('applicability_mode', 'all');
        if ($mode === 'all') {
            $standard->applicabilities()->delete();
            return;
        }
        $programIds = array_filter((array) $request->input('selected_programs', []));
        $unitIds = array_filter((array) $request->input('selected_units', []));
        $standard->applicabilities()->delete();
        foreach ($programIds as $pid) {
            $standard->applicabilities()->create([
                'target_type' => 'prodi',
                'academic_program_id' => $pid,
                'unit_id' => null,
            ]);
        }
        foreach ($unitIds as $uid) {
            $standard->applicabilities()->create([
                'target_type' => 'unit',
                'academic_program_id' => null,
                'unit_id' => $uid,
            ]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_standar'      => 'nullable|string|max:100',
            'document_id'       => 'nullable|exists:documents,id',
            'name'              => 'nullable|string|max:255',
            'pernyataan_standar'=> 'required|string',
            'rujukan'           => 'nullable|string|max:255',
            'type'              => 'nullable|in:IKU,IKT',
            'description'       => 'nullable|string',
            'iku'               => 'nullable|array',
            'iku.*.text'        => 'nullable|string|max:2000',
            'iku.*.target'      => 'nullable|string|max:255',
            'ikt'               => 'nullable|array',
            'ikt.*.text'        => 'nullable|string|max:2000',
            'ikt.*.target'      => 'nullable|string|max:255',
            'file'              => 'nullable|file|extensions:pdf,doc,docx,xls,xlsx,csv|max:10240',
            // Apabilitas standar
            'applicability_mode' => 'nullable|in:all,custom',
            'selected_programs'  => 'nullable|array',
            'selected_programs.*' => 'exists:academic_programs,id',
            'selected_units'     => 'nullable|array',
            'selected_units.*'   => 'exists:units,id',
        ], [
            'file.extensions' => 'Format dokumen harus berupa PDF, Word, Excel, atau CSV.',
            'file.max'        => 'Ukuran dokumen maksimal adalah 10 MB.',
        ]);

        if ($request->input('applicability_mode') === 'custom' && empty($request->input('selected_programs')) && empty($request->input('selected_units'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'selected_programs' => 'Pilih minimal satu Prodi atau Unit jika mode terbatas.',
            ]);
        }

        $validated['is_active'] = true;

        // Auto-fill name & kode_standar from selected Document
        $doc = \App\Models\Document::find($validated['document_id'] ?? null);
        $validated['name'] = $doc ? $doc->title : ($validated['name'] ?? null);
        if ($doc) {
            $count = QualityStandard::where('document_id', $doc->id)->count();
            $validated['kode_standar'] = $doc->code . '/' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        $validated['type'] = $validated['type'] ?? 'IKU';
        $validated['indicators'] = [
            'iku' => $this->indicatorRows($request->input('iku')),
            'ikt' => $this->indicatorRows($request->input('ikt')),
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            $ext = strtolower($file->getClientOriginalExtension());
            $importedCount = 0;
            
            // Cek jika rute ini lewat quick upload form (di mana user berharap impor data)
            if (empty($validated['pernyataan_standar']) || $validated['pernyataan_standar'] === '-') {
                // Gunakan OpenSpout untuk file XLSX/CSV
                if ($ext === 'xlsx' || $ext === 'csv') {
                    try {
                        if ($ext === 'csv') {
                           $reader = new \OpenSpout\Reader\CSV\Reader();
                        } else {
                           $reader = new \OpenSpout\Reader\XLSX\Reader();
                        }
                        $reader->open($filePath);
                        $lastStandard = null;
                        $ikuCounter = 1;
                        $iktCounter = 1;
                        // Mapping indeks dinamis agar anti-geser kolom
                        $headerMap = null;
                        
                        foreach ($reader->getSheetIterator() as $sheet) {
                            foreach ($sheet->getRowIterator() as $row) {
                                $cellsArray = $row->toArray();
                                $cellsText = strtolower(implode(' ', $cellsArray));
                                
                                // Deteksi apakah ini baris header
                                if (is_null($headerMap)) {
                                    if (str_contains($cellsText, 'standar') || str_contains($cellsText, 'indikator') || str_contains($cellsText, 'target') || str_contains($cellsText, 'kode')) {
                                        $headerMap = [];
                                        foreach($cellsArray as $i => $h) {
                                            $hstr = strtolower(trim((string)$h));
                                            if (str_contains($hstr, 'kode')) $headerMap['kode'] = $i;
                                            elseif (str_contains($hstr, 'nama')) $headerMap['nama'] = $i;
                                            elseif (str_contains($hstr, 'pernyata')) $headerMap['pernyataan'] = $i;
                                            elseif (str_contains($hstr, 'rujukan')) $headerMap['rujukan'] = $i;
                                            elseif (str_contains($hstr, 'utama') || str_contains($hstr, 'iku')) $headerMap['iku'] = $i;
                                            elseif (str_contains($hstr, 'tambahan') || str_contains($hstr, 'ikt')) $headerMap['ikt'] = $i;
                                            elseif (str_contains($hstr, 'target')) $headerMap['target'] = $i;
                                            elseif (str_contains($hstr, 'link') || str_contains($hstr, 'dokumen')) $headerMap['link'] = $i;
                                            elseif (str_contains($hstr, 'indikator') && !isset($headerMap['iku'])) $headerMap['iku'] = $i;
                                        }
                                        continue; 
                                    } else {
                                        // Jika tidak ada baris header yang terdeteksi, berasumsi urutan standar template baru
                                        $headerMap = ['kode'=>0, 'nama'=>1, 'pernyataan'=>2, 'rujukan'=>3, 'iku'=>4, 'ikt'=>5, 'target'=>6, 'link'=>7];
                                    }
                                }
                                
                                $cells = array_pad($cellsArray, 100, ''); // Cegah Out of bounds
                                
                                $kode       = trim((string)$cells[ $headerMap['kode'] ?? 99 ]);
                                $nama       = trim((string)$cells[ $headerMap['nama'] ?? 99 ]);
                                $pernyataan = trim((string)$cells[ $headerMap['pernyataan'] ?? 99 ]);
                                $rujukan    = trim((string)$cells[ $headerMap['rujukan'] ?? 99 ]);
                                $iku        = trim((string)$cells[ $headerMap['iku'] ?? 99 ]);
                                $ikt        = trim((string)$cells[ $headerMap['ikt'] ?? 99 ]);
                                $target     = trim((string)$cells[ $headerMap['target'] ?? 99 ]);
                                $linkFile   = trim((string)$cells[ $headerMap['link'] ?? 99 ]);
                                
                                if (!empty($nama) || !empty($pernyataan)) {
                                    if ($kode === 'Kode Standar') continue; // double safety if header wasn't skipped
                                    
                                    if (empty($nama)) $nama = $pernyataan;

                                    $ikuCounter = 1;
                                    $iktCounter = 1;
                                    
                                    $desc = "";
                                    if ($iku && $iku !== '-') { $desc .= "IKU $ikuCounter: $iku\n"; $ikuCounter++; }
                                    if ($ikt && $ikt !== '-') { $desc .= "IKT $iktCounter: $ikt\n"; $iktCounter++; }

                                    // Bersihkan target jika hanya berisi strip/hyphen
                                    if ($target === '-') $target = '';

                                    // Prepare link
                                    $fileData = [];
                                    if (!empty($linkFile) && $linkFile !== '-') {
                                        // jika ada teks tapi tidak diawali http, kasih peringatan / rapikan
                                        if (!str_starts_with($linkFile, 'http')) {
                                            $linkFile = 'https://' . $linkFile;
                                        }
                                        $fileData = [
                                            'file_path' => $linkFile,
                                            'file_name' => 'Dokumen Eksternal',
                                            'file_type' => 'link'
                                        ];
                                    }

                                    $indIku = ($iku && $iku !== '-') ? [['text' => $iku, 'target' => $target]] : [];
                                    $indIkt = ($ikt && $ikt !== '-') ? [['text' => $ikt, 'target' => $target]] : [];

                                    $lastStandard = QualityStandard::create(array_merge([
                                        'kode_standar'       => $kode,
                                        'name'               => $nama,
                                        'pernyataan_standar' => $pernyataan,
                                        'rujukan'            => $rujukan,
                                        'type'               => 'IKU',
                                        'target_value'       => $target,
                                        'description'        => trim($desc),
                                        'indicators'         => ['iku' => $indIku, 'ikt' => $indIkt],
                                        'is_active'          => true,
                                        'document_id'         => $validated['document_id'] ?? null,
                                    ], $fileData));
                                    $importedCount++;
                                } else {
                                    // Jika baris kosong tapi ada lanjuan indikator (merge row)
                                    if ($lastStandard && (!empty($iku) || !empty($ikt))) {
                                        $desc = "";
                                        if ($iku && $iku !== '-') { $desc .= "\n---\nIKU $ikuCounter: $iku"; $ikuCounter++; }
                                        if ($ikt && $ikt !== '-') { $desc .= "\n---\nIKT $iktCounter: $ikt"; $iktCounter++; }
                                        
                                        $lastStandard->description .= $desc;
                                        if (!empty($target) && $target !== '-') {
                                            $lastStandard->target_value .= ($lastStandard->target_value ? "\n---\n" : "") . $target;
                                        }

                                        $indicatorData = is_array($lastStandard->indicators) ? $lastStandard->indicators : ['iku' => [], 'ikt' => []];
                                        if ($iku && $iku !== '-') {
                                            $indicatorData['iku'][] = ['text' => $iku, 'target' => ($target && $target !== '-') ? $target : ''];
                                        }
                                        if ($ikt && $ikt !== '-') {
                                            $indicatorData['ikt'][] = ['text' => $ikt, 'target' => ($target && $target !== '-') ? $target : ''];
                                        }
                                        $lastStandard->indicators = $indicatorData;

                                        $lastStandard->save();
                                    }
                                }
                            }
                            break; // hanya sheet pertama
                        }
                        $reader->close();
                    } catch (\Exception $e) { /* ignore to fallback */ }
                } 
                
                if ($importedCount > 0) {
                    return redirect()->route('admin.quality-standards.index')->with('success', "$importedCount Standar Mutu berhasil diimpor dari dokumen.");
                } else {
                    return redirect()->route('admin.quality-standards.index')->with('error', 'Gagal membaca data dari dokumen. Anda wajib menggunakan Template EXCEL (.xlsx) terbaru dan memastikan kolom tidak diubah.');
                }
            }
            
            // Menyimpan file jika tidak diimpor atau berasal dari form Modal biasa
            $validated['file_path'] = $file->store('quality_standards', 'local');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $fileExt = strtolower($file->getClientOriginalExtension());
            $validated['file_type'] = in_array($fileExt, ['xls', 'xlsx', 'csv']) ? 'excel'
                : (in_array($fileExt, ['doc', 'docx']) ? 'word' : 'pdf');
        }

        $standard = QualityStandard::create($validated);
        $this->syncApplicabilitiesFromRequest($standard, $request);

        return redirect()->route('admin.quality-standards.index')->with('success', 'Standar Mutu berhasil ditambahkan.');
    }

    public function show(QualityStandard $qualityStandard)
    {
        return redirect()->route('admin.quality-standards.edit', $qualityStandard);
    }

    public function edit(QualityStandard $qualityStandard)
    {
        $qualityStandard->load(['applicabilities', 'applicablePrograms', 'applicableUnits', 'parent']);
        $programs = AcademicProgram::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        // Hanya tampilkan Dokumen Mutu yang sudah aktif (sudah di-SK-kan oleh pimpinan)
        $documents = \App\Models\Document::where('module', 'dokumen_mutu')
            ->where('status', 'aktif')
            ->with('decree')
            ->orderBy('code')
            ->get();

        return view('admin.quality_standards.edit', compact('qualityStandard', 'programs', 'units', 'documents')); 
    }

    public function update(Request $request, QualityStandard $qualityStandard)
    {
        $validated = $request->validate([
            'kode_standar'      => 'nullable|string|max:100',
            'document_id'       => 'nullable|exists:documents,id',
            'name'              => 'nullable|string|max:255',
            'pernyataan_standar'=> 'required|string',
            'rujukan'           => 'nullable|string|max:255',
            'type'              => 'nullable|in:IKU,IKT',
            'description'       => 'nullable|string',
            'iku'               => 'nullable|array',
            'iku.*.text'        => 'nullable|string|max:2000',
            'iku.*.target'      => 'nullable|string|max:255',
            'ikt'               => 'nullable|array',
            'ikt.*.text'        => 'nullable|string|max:2000',
            'ikt.*.target'      => 'nullable|string|max:255',
            'file'              => 'nullable|file|extensions:pdf,doc,docx,xls,xlsx,csv|max:10240',
            // Apabilitas standar
            'applicability_mode' => 'nullable|in:all,custom',
            'selected_programs'  => 'nullable|array',
            'selected_programs.*' => 'exists:academic_programs,id',
            'selected_units'     => 'nullable|array',
            'selected_units.*'   => 'exists:units,id',
        ], [
            'file.extensions' => 'Format dokumen harus berupa PDF, Word, Excel, atau CSV.',
            'file.max'        => 'Ukuran dokumen maksimal adalah 10 MB.',
        ]);

        if ($request->input('applicability_mode') === 'custom' && empty($request->input('selected_programs')) && empty($request->input('selected_units'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'selected_programs' => 'Pilih minimal satu Prodi atau Unit jika mode terbatas.',
            ]);
        }

        $validated['is_active'] = $request->boolean('is_active');

        // Auto-fill name & kode_standar from selected Document
        $doc = \App\Models\Document::find($validated['document_id'] ?? null);
        if ($doc) {
            $validated['name'] = $doc->title;
        } elseif (!array_key_exists('name', $validated) || $validated['name'] === null) {
            // Tidak ada nama pengganti: jangan timpa name lama dengan null
            unset($validated['name']);
        }

        // Regenerate kode_standar only if document changed
        if ($doc && $qualityStandard->document_id != $doc->id) {
            $count = QualityStandard::where('document_id', $doc->id)->count();
            $validated['kode_standar'] = $doc->code . '/' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        }

        $validated['type'] = $validated['type'] ?? ($qualityStandard->type ?? 'IKU');
        $validated['indicators'] = [
            'iku' => $this->indicatorRows($request->input('iku')),
            'ikt' => $this->indicatorRows($request->input('ikt')),
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($qualityStandard->file_path && Storage::disk('local')->exists($qualityStandard->file_path)) {
                Storage::disk('local')->delete($qualityStandard->file_path);
            }

            $file = $request->file('file');
            $validated['file_path'] = $file->store('quality_standards', 'local');
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_size'] = $file->getSize();
            $ext = strtolower($file->getClientOriginalExtension());
            $validated['file_type'] = in_array($ext, ['xls', 'xlsx']) ? 'excel'
                : (in_array($ext, ['doc', 'docx']) ? 'word' : 'pdf');
        }

        // Snapshot versi lama sebelum diubah (versioning / riwayat revisi)
        StandardVersion::create([
            'quality_standard_id' => $qualityStandard->id,
            'version'             => $qualityStandard->version ?? 1,
            'snapshot'            => $qualityStandard->only([
                'kode_standar', 'pernyataan_standar', 'rujukan', 'name', 'type',
                'document_id', 'description', 'target_value', 'indicators', 'is_active', 'file_path', 'file_name',
            ]),
            'changed_by'          => auth()->id(),
        ]);

        // Naikkan nomor versi
        $validated['version'] = ($qualityStandard->version ?? 1) + 1;

        // Setiap edit = revisi: status draft sampai SK Perubahan (P5.2) ditandatangani Pimpinan
        $validated['revisi_status'] = 'draft_revisi';

        $qualityStandard->update($validated);
        $this->syncApplicabilitiesFromRequest($qualityStandard, $request);

        // Jika datang dari alur P5.1 (Revisi), kembali ke halaman Peninjauan & Revisi Standar
        $redirect = $request->input('dari') === 'revisi'
            ? route('admin.revisi-standar.index')
            : route('admin.quality-standards.index');

        return redirect($redirect)->with('success', 'Standar Mutu berhasil diperbarui (Revisi v' . $validated['version'] . ').');
    }

    public function destroy(QualityStandard $qualityStandard)
    {
        if ($qualityStandard->file_path && Storage::disk('local')->exists($qualityStandard->file_path)) {
            Storage::disk('local')->delete($qualityStandard->file_path);
        }
        $qualityStandard->delete();
        return redirect()->route('admin.quality-standards.index')->with('success', 'Standar Mutu berhasil dihapus.');
    }

    /**
     * Download the uploaded file for a standard
     */
    public function downloadFile(QualityStandard $qualityStandard)
    {
        if (!$qualityStandard->file_path || !Storage::disk('local')->exists($qualityStandard->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }
        return Storage::disk('local')->download($qualityStandard->file_path, $qualityStandard->file_name);
    }

    /**
     * Normalisasi baris indikator dari input form (iku/ikt).
     * Buang baris tanpa teks, susun ulang index berurutan.
     */
    private function indicatorRows($rows): array
    {
        $out = [];
        $rows = is_array($rows) ? $rows : [];

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $text = trim((string)($row['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $out[] = [
                'text'   => $text,
                'target' => trim((string)($row['target'] ?? '')),
            ];
        }

        return $out;
    }

    /**
     * Download a blank template (Word or Excel)
     */
    public function downloadTemplate(Request $request)
    {
        $format = $request->query('format', 'xlsx');

        if ($format === 'xlsx') {
            return $this->generateExcelTemplate();
        } elseif ($format === 'docx') {
            return $this->generateWordTemplate();
        } else {
            return $this->generatePdfTemplate();
        }
    }

    private function generateExcelTemplate()
    {
        // Ambil master data standar & indikator dari sistem (SPMI/Admin telah input)
        $standards = \App\Models\QualityStandard::where('is_active', true)->orderBy('kode_standar')->get();
        $standarNames = $standards->pluck('name')->filter()->unique()->values()->toArray();
        $ikuList = $standards->flatMap(fn($s) => collect($s->ikuIndicators())->pluck('text'))->filter()->unique()->values()->toArray();
        $iktList = $standards->flatMap(fn($s) => collect($s->iktIndicators())->pluck('text'))->filter()->unique()->values()->toArray();

        // Fallback jika belum ada master data
        if (empty($standarNames)) {
            $standarNames = ['Standar Pendidikan', 'Standar Penelitian', 'Standar Pengabdian'];
        }
        if (empty($ikuList)) {
            $ikuList = ['Capaian pembelajaran sesuai KKNI', '80% dosen aktif tiap tahun'];
        }

        // Coba generate dengan PhpSpreadsheet (dropdown dari master data)
        try {
            if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Template');

                // Header
                $headers = ['Kode Standar', 'Nama Standar', 'Pernyataan Standar', 'Rujukan', 'Indikator Kinerja Utama (IKU)', 'Indikator Kinerja Tambahan (IKT)', 'Target', 'Link Dokumen'];
                $colLetters = ['A','B','C','D','E','F','G','H'];
                foreach ($headers as $i => $h) {
                    $cell = $colLetters[$i] . '1';
                    $sheet->setCellValue($cell, $h);
                    $sheet->getStyle($cell)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                    $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1A56A0');
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }
                // Contoh baris
                $sheet->setCellValue('A2', 'S.01');
                $sheet->setCellValue('B2', $standarNames[0] ?? 'Standar Pendidikan');
                $sheet->setCellValue('C2', 'Melaksanakan Tri Dharma Perguruan Tinggi secara optimal');
                $sheet->setCellValue('D2', 'Permendikbud No.3 Th 2020');
                $sheet->setCellValue('E2', $ikuList[0] ?? '80% dosen aktif tiap tahun');
                $sheet->setCellValue('F2', $iktList[0] ?? '-');
                $sheet->setCellValue('G2', '80%');
                $sheet->setCellValue('H2', 'https://docs.google.com/...');

                // Lebar kolom
                $widths = [14, 28, 35, 22, 32, 32, 12, 28];
                foreach ($widths as $i => $w) {
                    $sheet->getColumnDimension($colLetters[$i])->setWidth($w);
                }
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:H1');

                // Sheet tersembunyi untuk list dropdown
                $listSheet = $spreadsheet->createSheet();
                $listSheet->setTitle('MasterData');
                // Kolom A = Standar
                $listSheet->setCellValue('A1', 'Daftar Standar');
                foreach ($standarNames as $idx => $name) {
                    $listSheet->setCellValue('A' . ($idx + 2), $name);
                }
                // Kolom B = IKU
                $listSheet->setCellValue('B1', 'Daftar IKU');
                foreach ($ikuList as $idx => $txt) {
                    $listSheet->setCellValue('B' . ($idx + 2), $txt);
                }
                // Kolom C = IKT
                $listSheet->setCellValue('C1', 'Daftar IKT');
                foreach ($iktList as $idx => $txt) {
                    $listSheet->setCellValue('C' . ($idx + 2), $txt);
                }
                foreach (['A','B','C'] as $col) {
                    $listSheet->getColumnDimension($col)->setWidth(40);
                }
                $listSheet->getStyle('A1:C1')->getFont()->setBold(true);
                // Sembunyikan sheet MasterData tapi tetap bisa dipakai validasi
                $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                // Helper buat validasi list
                $makeValidation = function($formula) {
                    $v = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
                    $v->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $v->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $v->setAllowBlank(true);
                    $v->setShowInputMessage(true);
                    $v->setShowErrorMessage(true);
                    $v->setShowDropDown(true);
                    $v->setErrorTitle('Pilihan tidak valid');
                    $v->setError('Pilih dari daftar dropdown yang tersedia.');
                    $v->setPromptTitle('Pilih dari daftar');
                    $v->setPrompt('Pilih nilai dari daftar master data.');
                    $v->setFormula1($formula);
                    return $v;
                };

                $maxRow = 1000;
                $standarCount = count($standarNames);
                $ikuCount = count($ikuList);
                $iktCount = count($iktList);

                // Nama Standar (kolom B) dropdown dari MasterData!$A$2:$A$N
                if ($standarCount > 0) {
                    $formulaStandar = 'MasterData!$A$2:$A$' . ($standarCount + 1);
                    for ($row = 2; $row <= $maxRow; $row++) {
                        $sheet->getCell('B' . $row)->setDataValidation(clone $makeValidation($formulaStandar));
                    }
                }
                // IKU (kolom E)
                if ($ikuCount > 0) {
                    $formulaIku = 'MasterData!$B$2:$B$' . ($ikuCount + 1);
                    for ($row = 2; $row <= $maxRow; $row++) {
                        $sheet->getCell('E' . $row)->setDataValidation(clone $makeValidation($formulaIku));
                    }
                }
                // IKT (kolom F) — jika ada
                if ($iktCount > 0) {
                    $formulaIkt = 'MasterData!$C$2:$C$' . ($iktCount + 1);
                    for ($row = 2; $row <= $maxRow; $row++) {
                        $sheet->getCell('F' . $row)->setDataValidation(clone $makeValidation($formulaIkt));
                    }
                }

                // Komentar instruksi di header
                $sheet->getComment('B1')->getText()->createTextRun('Pilih Standar dari dropdown (data dari sistem)');
                $sheet->getComment('E1')->getText()->createTextRun('Pilih IKU dari daftar master data');
                $sheet->getComment('F1')->getText()->createTextRun('Pilih IKT dari daftar master data (opsional)');

                $spreadsheet->setActiveSheetIndex(0);
                $tempPath = storage_path('app/template_standar_mutu.xlsx');
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($tempPath);

                return response()->download($tempPath, 'template_standar_mutu.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])->deleteFileAfterSend(false);
            }
        } catch (\Exception $e) {
            // Log dan fallback ke OpenSpout/CSV
            \Illuminate\Support\Facades\Log::warning('Gagal generate template PhpSpreadsheet: ' . $e->getMessage());
        }

        // Fallback: OpenSpout sederhana (tanpa dropdown) jika PhpSpreadsheet gagal
        $tempPath = storage_path('app/template_standar_mutu.xlsx');
        try {
            if (class_exists('\OpenSpout\Writer\XLSX\Writer')) {
                $writer = new \OpenSpout\Writer\XLSX\Writer();
                $writer->openToFile($tempPath);
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    'Kode Standar', 'Nama Standar', 'Pernyataan Standar', 'Rujukan',
                    'Indikator Kinerja Utama (IKU)', 'Indikator Kinerja Tambahan (IKT)', 'Target', 'Link Dokumen'
                ]));
                $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues([
                    'S.01', 'Standar Pendidikan', 'Melaksanakan Tri Dharma Perguruan Tinggi secara optimal', 'Permendikbud No.3 Th 2020',
                    '80% dosen aktif tiap tahun', '-', '80%', 'https://docs.google.com/...'
                ]));
                $writer->close();
                return response()->download($tempPath, 'template_standar_mutu.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {}

        $rows = [
            ['Kode Standar', 'Nama Standar', 'Pernyataan Standar', 'Rujukan', 'Indikator Kinerja Utama (IKU)', 'Indikator Kinerja Tambahan (IKT)', 'Target', 'Link Dokumen'],
            ['S.01', 'Standar Pendidikan', 'Contoh pernyataan standar...', 'Permendikbud No. XX/YYYY', 'Capaian pembelajaran sesuai KKNI', '-', '≥ 80%', 'https://url...'],
        ];
        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF);
        foreach ($rows as $row) {
            $csv .= implode(';', array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', $row)) . "\n";
        }
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_standar_mutu.csv"',
        ]);
    }

    private function generateWordTemplate()
    {
        // Generate a simple HTML that can be opened in Word
        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Template Standar Mutu SPMI</title>
<style>
body { font-family: Arial, sans-serif; margin: 40px; }
h2 { color: #1a56a0; text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th { background-color: #1a56a0; color: white; padding: 10px; text-align: left; font-size: 13px; }
td { border: 1px solid #ccc; padding: 8px; font-size: 12px; vertical-align: top; min-height: 30px; }
th { border: 1px solid #0d3d7a; }
.note { font-size: 11px; color: #666; margin-top: 10px; }
</style>
</head>
<body>
<h2>TEMPLATE STANDAR MUTU SPMI</h2>
<p style="text-align:center; color:#555; font-size:13px;">Silakan isi tabel berikut sesuai dengan standar mutu institusi Anda</p>
<table>
<thead>
<tr>
<th width="10%">Kode Standar</th>
<th width="25%">Pernyataan Standar</th>
<th width="20%">Rujukan</th>
<th width="18%">Indikator Kinerja Utama (IKU)</th>
<th width="17%">Indikator Kinerja Tambahan (IKT)</th>
<th width="10%">Target</th>
</tr>
</thead>
<tbody>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
</tbody>
</table>
<p class="note">Keterangan:<br>
- IKU: Indikator Kinerja Utama (wajib dipenuhi)<br>
- IKT: Indikator Kinerja Tambahan (pelengkap/opsional)<br>
- Target: nilai capaian yang diharapkan (contoh: &ge; 3.00, 100%, dsb.)
</p>
</body>
</html>';

        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="template_standar_mutu.doc"',
        ]);
    }

    private function generatePdfTemplate()
    {
        // Plain text PDF fallback — just return HTML with PDF mimetype hint
        // Since we don't have a PDF library, we return the Word template but named as doc
        // and label it a simple text document
        $content = "TEMPLATE STANDAR MUTU SPMI\n\n";
        $content .= str_repeat("=", 80) . "\n";
        $content .= sprintf("%-12s %-30s %-20s %-20s %-20s %-10s\n",
            "Kode Standar", "Pernyataan Standar", "Rujukan", "IKU", "IKT", "Target");
        $content .= str_repeat("-", 120) . "\n";
        for ($i = 0; $i < 10; $i++) {
            $content .= sprintf("%-12s %-30s %-20s %-20s %-20s %-10s\n", "", "", "", "", "", "");
        }
        $content .= "\nKeterangan:\n";
        $content .= "- IKU: Indikator Kinerja Utama\n";
        $content .= "- IKT: Indikator Kinerja Tambahan\n";
        $content .= "- Target: nilai capaian yang diharapkan\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="template_standar_mutu.txt"',
        ]);
    }

    /**
     * Sinkronisasi applicabilities standar.
     */
    private function syncApplicabilities(QualityStandard $standard, array $applicabilities): void
    {
        // Hapus mapping lama
        $standard->applicabilities()->delete();

        // Buat mapping baru jika ada
        foreach ($applicabilities as $app) {
            if (!empty($app['target_type'])) {
                $standard->applicabilities()->create([
                    'target_type' => $app['target_type'],
                    'academic_program_id' => $app['target_type'] === 'prodi' ? ($app['academic_program_id'] ?? null) : null,
                    'unit_id' => $app['target_type'] === 'unit' ? ($app['unit_id'] ?? null) : null,
                ]);
            }
        }
    }
}
