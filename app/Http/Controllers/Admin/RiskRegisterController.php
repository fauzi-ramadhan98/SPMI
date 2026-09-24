<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiskRegister;
use App\Models\AcademicProgram;
use App\Models\AcademicYear;
use App\Models\AuditAssignment;
use App\Models\RiskRegisterAssignment;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RiskRegisterAssigned;
use App\Notifications\RiskRevisionRequested;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class RiskRegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = RiskRegister::with(['academicProgram', 'unit', 'user', 'validatedBy'])->orderBy('created_at', 'desc');
        $user = auth()->user();

        if ($user->hasRole('prodi')) {
            $query->where('academic_program_id', $user->academic_program_id);
        } elseif ($user->hasRole('unit')) {
            $query->where('unit_id', $user->unit_id);
        } elseif ($user->hasRole('auditor')) {
            $assignedProdiIds = AuditAssignment::where('auditor_id', $user->id)
                ->whereNotNull('academic_program_id')
                ->pluck('academic_program_id');
            $assignedUnitIds = AuditAssignment::where('auditor_id', $user->id)
                ->whereNotNull('unit_id')
                ->pluck('unit_id');
            $query->where(function ($q) use ($assignedProdiIds, $assignedUnitIds) {
                $q->whereIn('academic_program_id', $assignedProdiIds)
                  ->orWhereIn('unit_id', $assignedUnitIds);
            });
        }

        // Filter toolbar (SPMI / pimpinan) — seluruh risiko semua prodi
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('academic_program_id')) {
            $query->where('academic_program_id', $request->academic_program_id);
        }
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }
        if ($request->filled('risk_category')) {
            $query->where('risk_category', $request->risk_category);
        }

        $risks = $query->paginate(10)->withQueryString();

        $programs = AcademicProgram::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::active();
        $yearOptions = RiskRegister::distinct()->orderByDesc('academic_year')->pluck('academic_year');
        $filters = $request->only(['academic_year', 'semester', 'academic_program_id', 'risk_level', 'risk_category']);

        // Poin catatan client: "Prodi tiap semester harus isi risk register
        // tapi ditugaskan oleh SPMI" — daftar penugasan (SPMI) & penugasan milik saya.
        $assignments = collect();
        $myAssignment = null;
        if ($user->hasRole('spmi')) {
            $assignments = RiskRegisterAssignment::with(['academicProgram', 'unit'])->orderByDesc('id')->get();
        } elseif ($user->hasRole('prodi') && $user->academic_program_id) {
            $myAssignment = RiskRegisterAssignment::where('academic_program_id', $user->academic_program_id)->latest('id')->first();
        } elseif ($user->hasRole('unit') && $user->unit_id) {
            $myAssignment = RiskRegisterAssignment::where('unit_id', $user->unit_id)->latest('id')->first();
        }

        return view('admin.risk_registers.index', compact('risks', 'programs', 'units', 'academicYears', 'yearOptions', 'filters', 'assignments', 'myAssignment'));
    }

    public function create()
    {
        $this->authorizeWrite();

        $user = auth()->user();
        $lockedProgramId = null;
        $lockedUnitId = null;

        if ($user->hasRole('prodi')) {
            $programs = AcademicProgram::where('id', $user->academic_program_id)->get();
            $units = collect();
            $lockedProgramId = $user->academic_program_id;
        } elseif ($user->hasRole('unit')) {
            $programs = collect();
            $units = Unit::where('id', $user->unit_id)->get();
            $lockedUnitId = $user->unit_id;
        } else {
            $programs = AcademicProgram::all();
            $units = Unit::all();
        }

        $standards = \App\Models\QualityStandard::where('is_active', true)
            ->forUser(auth()->user())
            ->get();
        $academicYears = AcademicYear::active();

        $defaultSemester = $this->assignedSemesterFor($user);

        return view('admin.risk_registers.create', compact('programs', 'units', 'standards', 'academicYears', 'lockedProgramId', 'lockedUnitId', 'defaultSemester'));
    }

    public function store(Request $request)
    {
        $this->authorizeWrite();

        $validated = $this->validateRiskInput($request);

        $validated['risk_score'] = $validated['probability'] * $validated['impact'];
        $validated['risk_level'] = $this->riskLevel($validated['risk_score']);
        $validated['created_by'] = auth()->id();
        // mapping quality_standard_id jika ada
        $qs = \App\Models\QualityStandard::where('name', $validated['standar_mutu'])->first();
        if ($qs) $validated['quality_standard_id'] = $qs->id;

        RiskRegister::create($validated);
        return redirect()->route('admin.risk-registers.index')->with('success', 'Profil Risiko berhasil ditambahkan.');
    }

    public function show(RiskRegister $riskRegister)
    {
        return redirect()->route('admin.risk-registers.edit', $riskRegister);
    }

    public function edit(RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);

        $user = auth()->user();
        $lockedProgramId = null;
        $lockedUnitId = null;

        if ($user->hasRole('prodi')) {
            $programs = AcademicProgram::where('id', $user->academic_program_id)->get();
            $units = collect();
            $lockedProgramId = $user->academic_program_id;
        } elseif ($user->hasRole('unit')) {
            $programs = collect();
            $units = Unit::where('id', $user->unit_id)->get();
            $lockedUnitId = $user->unit_id;
        } else {
            $programs = AcademicProgram::all();
            $units = Unit::all();
        }

        $standards = \App\Models\QualityStandard::where('is_active', true)
            ->forUser(auth()->user())
            ->get();
        $academicYears = AcademicYear::active();

        $defaultSemester = $this->assignedSemesterFor($user);

        return view('admin.risk_registers.edit', compact('riskRegister', 'programs', 'units', 'standards', 'academicYears', 'lockedProgramId', 'lockedUnitId', 'defaultSemester'));
    }

    public function update(Request $request, RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);

        $validated = $this->validateRiskInput($request);

        $validated['risk_score'] = $validated['probability'] * $validated['impact'];
        $validated['risk_level'] = $this->riskLevel($validated['risk_score']);

        $riskRegister->update($validated + ['status' => 'pending', 'status_note' => null]);
        return redirect()->route('admin.risk-registers.index')->with('success', 'Profil Risiko berhasil diperbarui.');
    }

    /**
     * SPMI: Validasi / menyetujui mitigasi Risk Owner.
     */
    public function validateRisk(Request $request, RiskRegister $riskRegister)
    {
        abort_unless(auth()->user()->hasRole('spmi'), 403, 'Hanya SPMI yang dapat memvalidasi profil risiko.');

        $riskRegister->update([
            'status' => 'approved',
            'status_note' => null,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Profil risiko disetujui (Validasi SPMI).');
    }

    /**
     * SPMI: Beri Catatan → Risk Owner wajib merevisi, dan mendapat notifikasi di dasbor.
     */
    public function note(Request $request, RiskRegister $riskRegister)
    {
        abort_unless(auth()->user()->hasRole('spmi'), 403, 'Hanya SPMI yang dapat memberi catatan revisi.');

        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ], ['note.required' => 'Catatan revisi wajib diisi.']);

        $riskRegister->update([
            'status' => 'revision',
            'status_note' => $validated['note'],
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        $this->notifyOwnerRevision($riskRegister, $validated['note']);

        return back()->with('success', 'Catatan dikirim ke Risk Owner. Status risiko menjadi "Perlu Revisi".');
    }

    public function destroy(RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);
        $riskRegister->delete();
        return redirect()->route('admin.risk-registers.index')->with('success', 'Profil Risiko berhasil dihapus.');
    }

    public function updateDocumentLink(Request $request, RiskRegister $riskRegister)
    {
        $this->authorizeWrite();
        $this->authorizeAccess($riskRegister);
        $request->validate(['document_link' => 'nullable|url']);
        $riskRegister->update(['document_link' => $request->document_link]);
        return back()->with('success', 'Link bukti dokumen berhasil disimpan.');
    }

    private function validateRiskInput(Request $request)
    {
        $yearNames = AcademicYear::active()->pluck('name')->all();

        $validated = $request->validate([
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'unit_id'             => 'nullable|exists:units,id',
            'academic_year'       => ['required', 'string', Rule::in($yearNames)],
            // Poin catatan client: RR diisi Prodi tiap semester
            'semester'            => 'required|in:Ganjil,Genap',
            'standar_mutu'        => 'required|string|max:255',
            'butir_tilik'         => 'required|string',
            'risk_description'    => 'required|string',
            'temuan'              => 'required|string',
            'akar_masalah'        => 'required|string',
            'risk_category'       => 'required|in:Operasional,SDM,Keuangan,Teknologi,Kepatuhan,Reputasi',
            'impact'              => 'required|integer|min:1|max:5',
            'probability'         => 'required|integer|min:1|max:5',
            'mitigation_plan'     => 'required|string',
            'document_link'       => 'nullable|url',
            'pic'                 => 'required|string|max:255',
            'target_date'         => 'required|date',
        ], [
            'academic_year.required'     => 'Tahun Akademik wajib dipilih.',
            'academic_year.in'           => 'Tahun Akademik yang dipilih tidak valid. Pilih dari daftar Master Tahun Akademik.',
            'semester.required'          => 'Semester wajib dipilih (Ganjil/Genap).',
            'semester.in'                => 'Semester hanya boleh Ganjil atau Genap.',
            'akar_masalah.required'      => 'Akar Masalah wajib diisi.',
            'mitigation_plan.required' => 'Mitigasi wajib diisi. Jika ada risiko, harus ada rencana penanganannya.',
            'pic.required'             => 'PIC (Penanggungjawab) wajib diisi.',
            'target_date.required'     => 'Tanggal Penyelesaian wajib diisi.',
        ]);

        // Wajib memilih salah satu pemilik: prodi ATAU unit
        if (!$request->filled('academic_program_id') && !$request->filled('unit_id')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'academic_program_id' => 'Pilih salah satu pemilik risiko: Program Studi atau Unit Kerja.',
            ]);
        }

        return $validated;
    }

    private function riskLevel(int $score): string
    {
        if ($score >= 15) {
            return 'High';
        } elseif ($score >= 8) {
            return 'Medium';
        }
        return 'Low';
    }

    /**
     * Prodi/Unit hanya dapat mengelola profil risiko miliknya sendiri.
     */
    private function authorizeAccess(RiskRegister $riskRegister): void
    {
        $user = auth()->user();

        if ($user->hasRole('prodi') && $riskRegister->academic_program_id !== $user->academic_program_id) {
            abort(403, 'Anda tidak memiliki akses ke profil risiko ini.');
        }

        if ($user->hasRole('unit') && $riskRegister->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki akses ke profil risiko ini.');
        }

        // Auditor hanya dapat mengelola profil risiko prodi/unit yang ditugaskan
        if ($user->hasRole('auditor')) {
            $assigned = AuditAssignment::where('auditor_id', $user->id)
                ->where(function ($q) use ($riskRegister) {
                    $q->where('academic_program_id', $riskRegister->academic_program_id)
                      ->orWhere('unit_id', $riskRegister->unit_id);
                })
                ->exists();
            if (!$assigned) {
                abort(403, 'Anda tidak memiliki akses ke profil risiko ini.');
            }
        }
    }

    /**
     * Pengisian hanya boleh dilakukan Risk Owner (Prodi/Unit).
     * SPMI & Auditor hanya membaca; SPMI memvalidasi lewat action terpisah.
     */
    public function downloadTemplate()
    {
        $standards = \App\Models\QualityStandard::where('is_active', true)->orderBy('kode_standar')->get();
        $standarNames = $standards->pluck('name')->filter()->unique()->values()->toArray();
        $ikuList = $standards->flatMap(fn($s) => collect($s->ikuIndicators())->pluck('text'))->filter()->unique()->values()->toArray();
        $iktList = $standards->flatMap(fn($s) => collect($s->iktIndicators())->pluck('text'))->filter()->unique()->values()->toArray();
        $indikatorList = collect(array_merge($ikuList, $iktList))->map(fn($t) => mb_strimwidth($t, 0, 80, '...'))->filter()->unique()->values()->toArray();
        if (empty($standarNames)) $standarNames = ['Standar Kompetensi Lulusan', 'Standar Penelitian'];
        if (empty($indikatorList)) $indikatorList = ['IKU 1: Contoh indikator', 'IKT 1: Contoh IKT'];

        $kategories = ['Operasional','SDM','Keuangan','Teknologi','Kepatuhan','Reputasi'];
        $tahunOptions = \App\Models\AcademicYear::where('is_active', true)->pluck('name')->toArray();
        if (empty($tahunOptions)) $tahunOptions = ['2025/2026','2026/2027'];

        try {
            if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Template');

                $headers = ['Tahun Akademik','Standar Mutu','Indikator (Butir Tilik)','Deskripsi Risiko','Kondisi Saat Ini (Temuan)','Akar Masalah','Kategori Risiko','Impact (1-5)','Probability (1-5)','Rencana Mitigasi','PIC','Target Penyelesaian (YYYY-MM-DD)','Link Dokumen'];
                $colLetters = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];
                foreach ($headers as $i => $h) {
                    $cell = $colLetters[$i] . '1';
                    $sheet->setCellValue($cell, $h);
                    $sheet->getStyle($cell)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                    $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDC2626');
                    $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                }
                // Contoh baris
                $sheet->setCellValue('A2', $tahunOptions[0] ?? '2025/2026');
                $sheet->setCellValue('B2', $standarNames[0] ?? 'Standar Kompetensi Lulusan');
                $sheet->setCellValue('C2', $indikatorList[0] ?? 'IKU 1: Contoh');
                $sheet->setCellValue('D2', 'Risiko kekurangan dosen');
                $sheet->setCellValue('E2', 'Rasio dosen-mhs belum memenuhi');
                $sheet->setCellValue('F2', 'Keterbatasan rekrutmen');
                $sheet->setCellValue('G2', 'SDM');
                $sheet->setCellValue('H2', '4');
                $sheet->setCellValue('I2', '3');
                $sheet->setCellValue('J2', 'Mengajukan rekrutmen dosen baru');
                $sheet->setCellValue('K2', 'Kaprodi');
                $sheet->setCellValue('L2', date('Y-m-d', strtotime('+3 months')));
                $sheet->setCellValue('M2', 'https://drive.google.com/...');

                $widths = [14,28,32,28,28,28,14,12,14,28,16,20,28];
                foreach ($widths as $i => $w) $sheet->getColumnDimension($colLetters[$i])->setWidth($w);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter('A1:M1');

                // MasterData hidden
                $listSheet = $spreadsheet->createSheet();
                $listSheet->setTitle('MasterData');
                $listSheet->setCellValue('A1', 'Standar');
                foreach ($standarNames as $idx => $name) $listSheet->setCellValue('A'.($idx+2), $name);
                $listSheet->setCellValue('B1', 'Indikator');
                foreach ($indikatorList as $idx => $txt) $listSheet->setCellValue('B'.($idx+2), $txt);
                $listSheet->setCellValue('C1', 'Kategori');
                foreach ($kategories as $idx => $k) $listSheet->setCellValue('C'.($idx+2), $k);
                $listSheet->setCellValue('D1', 'Tahun');
                foreach ($tahunOptions as $idx => $t) $listSheet->setCellValue('D'.($idx+2), $t);
                // Impact & Probability 1-5
                $listSheet->setCellValue('E1', 'Nilai 1-5');
                for ($i=1;$i<=5;$i++) $listSheet->setCellValue('E'.($i+1), (string)$i);
                $listSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

                $makeValidation = function($formula) {
                    $v = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
                    $v->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $v->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $v->setAllowBlank(true);
                    $v->setShowInputMessage(true);
                    $v->setShowErrorMessage(true);
                    $v->setShowDropDown(true);
                    $v->setErrorTitle('Pilihan tidak valid');
                    $v->setError('Pilih dari daftar dropdown.');
                    $v->setPromptTitle('Pilih dari daftar');
                    $v->setPrompt('Pilih nilai dari daftar master data.');
                    $v->setFormula1($formula);
                    return $v;
                };
                $maxRow = 500;
                if (count($standarNames)>0) {
                    $f = 'MasterData!$A$2:$A$'.(count($standarNames)+1);
                    for ($r=2;$r<=$maxRow;$r++) $sheet->getCell('B'.$r)->setDataValidation(clone $makeValidation($f));
                }
                if (count($indikatorList)>0) {
                    $f = 'MasterData!$B$2:$B$'.(count($indikatorList)+1);
                    for ($r=2;$r<=$maxRow;$r++) $sheet->getCell('C'.$r)->setDataValidation(clone $makeValidation($f));
                }
                if (count($kategories)>0) {
                    $f = 'MasterData!$C$2:$C$'.(count($kategories)+1);
                    for ($r=2;$r<=$maxRow;$r++) $sheet->getCell('G'.$r)->setDataValidation(clone $makeValidation($f));
                }
                if (count($tahunOptions)>0) {
                    $f = 'MasterData!$D$2:$D$'.(count($tahunOptions)+1);
                    for ($r=2;$r<=$maxRow;$r++) $sheet->getCell('A'.$r)->setDataValidation(clone $makeValidation($f));
                }
                $f15 = 'MasterData!$E$2:$E$6';
                for ($r=2;$r<=$maxRow;$r++) {
                    $sheet->getCell('H'.$r)->setDataValidation(clone $makeValidation($f15));
                    $sheet->getCell('I'.$r)->setDataValidation(clone $makeValidation($f15));
                }
                // Komentar instruksi
                $sheet->getComment('B1')->getText()->createTextRun('Pilih Standar dari dropdown');
                $sheet->getComment('C1')->getText()->createTextRun('Pilih Indikator dari daftar master');
                $sheet->getComment('G1')->getText()->createTextRun('Pilih Kategori: Operasional/SDM/Keuangan/Teknologi/Kepatuhan/Reputasi');

                $spreadsheet->setActiveSheetIndex(0);
                $tempPath = storage_path('app/template_risk_register.xlsx');
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($tempPath);
                return response()->download($tempPath, 'template_risk_register.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ])->deleteFileAfterSend(false);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal generate template RiskRegister: '.$e->getMessage());
        }

        // Fallback CSV
        $rows = [
            ['Tahun Akademik','Standar Mutu','Indikator','Deskripsi Risiko','Kondisi Saat Ini','Akar Masalah','Kategori','Impact','Probability','Mitigasi','PIC','Target','Link'],
            [$tahunOptions[0]??'2025/2026','Standar Pendidikan','IKU 1: Contoh','Risiko contoh','Kondisi','Akar','SDM','4','3','Mitigasi','Kaprodi',date('Y-m-d'),'https://...'],
        ];
        $csv = chr(0xEF).chr(0xBB).chr(0xBF);
        foreach ($rows as $row) $csv .= implode(';', array_map(fn($v) => '"'.str_replace('"','""',$v).'"', $row))."\n";
        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_risk_register.csv"',
        ]);
    }

    public function import(Request $request)
    {
        $this->authorizeWrite();
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'Pilih file Excel/CSV terlebih dahulu.',
            'file.mimes' => 'Format harus xlsx, xls, atau csv.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        $user = auth()->user();
        $created = 0;
        $errors = [];

        try {
            if ($ext === 'csv') {
                $reader = new \OpenSpout\Reader\CSV\Reader();
            } else {
                $reader = new \OpenSpout\Reader\XLSX\Reader();
            }
            $reader->open($path);
            $headerMap = null;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $rowIdx => $row) {
                    $cells = $row->toArray();
                    $cellsText = strtolower(implode(' ', $cells));
                    if (is_null($headerMap)) {
                        if (str_contains($cellsText, 'standar') || str_contains($cellsText, 'risiko') || str_contains($cellsText, 'tahun')) {
                            $headerMap = [];
                            foreach ($cells as $i => $h) {
                                $hstr = strtolower(trim((string)$h));
                                if (str_contains($hstr, 'tahun')) $headerMap['tahun'] = $i;
                                elseif (str_contains($hstr, 'standar')) $headerMap['standar'] = $i;
                                elseif (str_contains($hstr, 'indikator')) $headerMap['indikator'] = $i;
                                elseif (str_contains($hstr, 'deskripsi risiko') || $hstr==='risiko') $headerMap['risiko'] = $i;
                                elseif (str_contains($hstr, 'kondisi')) $headerMap['temuan'] = $i;
                                elseif (str_contains($hstr, 'akar')) $headerMap['akar'] = $i;
                                elseif (str_contains($hstr, 'kategori')) $headerMap['kategori'] = $i;
                                elseif ($hstr==='p' || str_contains($hstr, 'probability')) $headerMap['p'] = $i;
                                elseif ($hstr==='d' || str_contains($hstr, 'impact')) $headerMap['impact'] = $i;
                                elseif (str_contains($hstr, 'mitigasi')) $headerMap['mitigasi'] = $i;
                                elseif ($hstr==='pic') $headerMap['pic'] = $i;
                                elseif (str_contains($hstr, 'target')) $headerMap['target'] = $i;
                                elseif (str_contains($hstr, 'link') || str_contains($hstr, 'dokumen')) $headerMap['link'] = $i;
                            }
                            continue;
                        } else {
                            $headerMap = ['tahun'=>0,'standar'=>1,'indikator'=>2,'risiko'=>3,'temuan'=>4,'akar'=>5,'kategori'=>6,'impact'=>7,'p'=>8,'mitigasi'=>9,'pic'=>10,'target'=>11,'link'=>12];
                        }
                    }
                    $cells = array_pad($cells, 20, '');
                    $tahun = trim((string)($cells[$headerMap['tahun'] ?? 99] ?? ''));
                    $standar = trim((string)($cells[$headerMap['standar'] ?? 99] ?? ''));
                    $indikator = trim((string)($cells[$headerMap['indikator'] ?? 99] ?? ''));
                    $risiko = trim((string)($cells[$headerMap['risiko'] ?? 99] ?? ''));
                    $temuan = trim((string)($cells[$headerMap['temuan'] ?? 99] ?? ''));
                    $akar = trim((string)($cells[$headerMap['akar'] ?? 99] ?? ''));
                    $kategori = trim((string)($cells[$headerMap['kategori'] ?? 99] ?? ''));
                    $impact = trim((string)($cells[$headerMap['impact'] ?? 99] ?? ''));
                    $prob = trim((string)($cells[$headerMap['p'] ?? 99] ?? ''));
                    $mitigasi = trim((string)($cells[$headerMap['mitigasi'] ?? 99] ?? ''));
                    $pic = trim((string)($cells[$headerMap['pic'] ?? 99] ?? ''));
                    $target = trim((string)($cells[$headerMap['target'] ?? 99] ?? ''));
                    $link = trim((string)($cells[$headerMap['link'] ?? 99] ?? ''));

                    if ($standar==='' && $risiko==='' && $indikator==='') continue;
                    if (strtolower($standar)==='tahun akademik' || strtolower($risiko)==='deskripsi risiko') continue;

                    if ($risiko==='' || $akar==='' || $mitigasi==='') {
                        $errors[] = "Baris ".($rowIdx).": Risiko/Akar/Mitigasi wajib diisi";
                        continue;
                    }
                    // Validasi kategori & angka
                    if ($kategori!=='' && !in_array($kategori, ['Operasional','SDM','Keuangan','Teknologi','Kepatuhan','Reputasi'])) {
                        $errors[] = "Baris ".($rowIdx).": Kategori tidak valid";
                        continue;
                    }
                    $impactInt = (int)$impact; $probInt = (int)$prob;
                    if ($impactInt<1||$impactInt>5||$probInt<1||$probInt>5) {
                        $errors[] = "Baris ".($rowIdx).": Impact/Probability harus 1-5";
                        continue;
                    }
                    // Tentukan pemilik
                    $data = [
                        'academic_year' => $tahun ?: ($this->getDefaultYear()),
                        'standar_mutu' => $standar ?: 'Standar Umum',
                        'butir_tilik' => $indikator ?: '-',
                        'risk_category' => $kategori ?: 'Operasional',
                        'risk_description' => $risiko,
                        'temuan' => $temuan ?: '-',
                        'akar_masalah' => $akar,
                        'impact' => $impactInt,
                        'probability' => $probInt,
                        'risk_score' => $impactInt*$probInt,
                        'risk_level' => $this->riskLevel($impactInt*$probInt),
                        'mitigation_plan' => $mitigasi,
                        'document_link' => $link ?: null,
                        'pic' => $pic ?: 'Kaprodi',
                        'target_date' => $target ?: date('Y-m-d', strtotime('+3 months')),
                        'created_by' => $user->id,
                    ];
                    if ($user->hasRole('prodi')) {
                        $data['academic_program_id'] = $user->academic_program_id;
                        $data['unit_id'] = null;
                    } elseif ($user->hasRole('unit')) {
                        $data['unit_id'] = $user->unit_id;
                        $data['academic_program_id'] = null;
                    } else {
                        // SPMI/Admin: coba tebak dari nama? default prodi pertama
                        $data['academic_program_id'] = AcademicProgram::first()?->id;
                    }
                    // Cari quality_standard_id jika ada
                    $qs = \App\Models\QualityStandard::where('name', $standar)->first();
                    if ($qs) $data['quality_standard_id'] = $qs->id;

                    RiskRegister::create($data);
                    $created++;
                }
                break;
            }
            $reader->close();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membaca file: '.$e->getMessage());
        }

        $msg = "$created risiko berhasil diimpor.";
        if (!empty($errors)) $msg .= " ".count($errors)." baris gagal: ".implode('; ', array_slice($errors,0,3));
        return redirect()->route('admin.risk-registers.index')->with($created>0?'success':'error', $msg);
    }

    private function getDefaultYear(): string
    {
        return AcademicYear::where('is_active', true)->first()?->name ?? (AcademicYear::first()?->name ?? date('Y').'/'.(date('Y')+1));
    }

    public function bulkStore(Request $request)
    {
        $this->authorizeWrite();
        $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.standar_mutu' => 'required|string',
            'rows.*.butir_tilik' => 'required|string',
            'rows.*.risk_description' => 'required|string',
            'rows.*.temuan' => 'required|string',
            'rows.*.akar_masalah' => 'required|string',
            'rows.*.risk_category' => 'required|in:Operasional,SDM,Keuangan,Teknologi,Kepatuhan,Reputasi',
            'rows.*.impact' => 'required|integer|min:1|max:5',
            'rows.*.probability' => 'required|integer|min:1|max:5',
            'rows.*.mitigation_plan' => 'required|string',
            'rows.*.pic' => 'required|string',
            'rows.*.target_date' => 'required|date',
            'rows.*.academic_year' => 'required|string',
            'rows.*.semester'      => 'nullable|in:Ganjil,Genap',
        ]);

        $count=0;
        foreach ($request->input('rows') as $row) {
            $data = $row;
            $data['risk_score'] = (int)$row['impact'] * (int)$row['probability'];
            $data['risk_level'] = $this->riskLevel($data['risk_score']);
            $data['created_by'] = auth()->id();
            $user = auth()->user();
            if ($user->hasRole('prodi')) {
                $data['academic_program_id'] = $user->academic_program_id;
                $data['unit_id'] = null;
            } elseif ($user->hasRole('unit')) {
                $data['unit_id'] = $user->unit_id;
                $data['academic_program_id'] = null;
            }
            $qs = \App\Models\QualityStandard::where('name', $row['standar_mutu'])->first();
            if ($qs) $data['quality_standard_id'] = $qs->id;
            $data['document_link'] = $row['document_link'] ?? null;
            RiskRegister::create($data);
            $count++;
        }
        return redirect()->route('admin.risk-registers.index')->with('success', "$count risiko berhasil disimpan via tabel dinamis.");
    }

    /**
     * Poin catatan client: SPMI menugaskan Prodi/Unit mengisi Risk Register tiap semester.
     */
    public function assignStore(Request $request)
    {
        abort_unless(auth()->user()->hasRole('spmi'), 403, 'Hanya SPMI yang dapat menugaskan pengisian Risk Register.');

        $yearNames = AcademicYear::active()->pluck('name')->all();
        $request->validate([
            'academic_year'       => ['required', 'string', Rule::in($yearNames)],
            'semester'            => 'required|in:Ganjil,Genap',
            'target'              => 'required|in:semua,prodi,unit',
            'academic_program_id' => 'required_if:target,prodi|nullable|exists:academic_programs,id',
            'unit_id'             => 'required_if:target,unit|nullable|exists:units,id',
            'note'                => 'nullable|string|max:500',
        ], [
            'academic_year.required'         => 'Tahun Akademik wajib dipilih.',
            'academic_year.in'               => 'Tahun Akademik tidak valid. Pilih dari Master Tahun Akademik.',
            'semester.required'              => 'Semester wajib dipilih (Ganjil/Genap).',
            'semester.in'                    => 'Semester hanya boleh Ganjil atau Genap.',
            'academic_program_id.required_if' => 'Pilih Program Studi yang ditugaskan.',
            'unit_id.required_if'            => 'Pilih Unit Kerja yang ditugaskan.',
        ]);

        $owners = [];
        if ($request->target === 'semua') {
            foreach (AcademicProgram::where('is_active', true)->get() as $program) {
                $owners[] = ['type' => 'prodi', 'id' => (int) $program->id];
            }
            foreach (Unit::where('is_active', true)->get() as $unit) {
                $owners[] = ['type' => 'unit', 'id' => (int) $unit->id];
            }
        } elseif ($request->target === 'prodi') {
            $owners[] = ['type' => 'prodi', 'id' => (int) $request->academic_program_id];
        } else {
            $owners[] = ['type' => 'unit', 'id' => (int) $request->unit_id];
        }

        $created = 0;
        $skipped = 0;

        foreach ($owners as $owner) {
            $ownerKey = $owner['type'] . '-' . $owner['id'];

            $exists = RiskRegisterAssignment::where('academic_year', $request->academic_year)
                ->where('semester', $request->semester)
                ->where('owner_key', $ownerKey)
                ->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $assignment = RiskRegisterAssignment::create([
                'academic_year'       => $request->academic_year,
                'semester'            => $request->semester,
                'owner_key'           => $ownerKey,
                'academic_program_id' => $owner['type'] === 'prodi' ? $owner['id'] : null,
                'unit_id'             => $owner['type'] === 'unit' ? $owner['id'] : null,
                'note'                => $request->note,
                'assigned_by'         => auth()->id(),
            ]);
            $created++;

            // Notifikasi ke akun Ka Prodi / Unit yang bersangkutan
            $ownerAccounts = $owner['type'] === 'prodi'
                ? User::role('prodi')->where('academic_program_id', $owner['id'])->get()
                : User::role('unit')->where('unit_id', $owner['id'])->get();
            if ($ownerAccounts->isNotEmpty()) {
                Notification::send($ownerAccounts, new RiskRegisterAssigned($assignment));
            }
        }

        $message = "Penugasan Pengisian Risk Register Semester {$request->semester} T.A. {$request->academic_year}: $created penugasan dibuat";
        if ($skipped) {
            $message .= ", $skipped sudah ditugaskan sebelumnya";
        }

        return back()->with('success', $message . '.');
    }

    public function assignDestroy(RiskRegisterAssignment $assignment)
    {
        abort_unless(auth()->user()->hasRole('spmi'), 403, 'Hanya SPMI yang dapat mencabut penugasan Risk Register.');

        $assignment->delete();

        return back()->with('success', 'Penugasan pengisian Risk Register dicabut.');
    }

    /** Semester terakhir yang ditugaskan SPMI untuk pemilik (pre-select di form). */
    private function assignedSemesterFor(User $user): ?string
    {
        $query = RiskRegisterAssignment::query();

        if ($user->hasRole('prodi') && $user->academic_program_id) {
            $query->where('academic_program_id', $user->academic_program_id);
        } elseif ($user->hasRole('unit') && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        } else {
            return null;
        }

        return $query->latest('id')->value('semester');
    }

    private function authorizeWrite(): void
    {
        $user = auth()->user();
        if ($user->hasRole('auditor') || $user->hasRole('spmi')) {
            abort(403, 'Risk Register bersifat read-only untuk SPMI/Auditor. Pengisian risiko adalah tugas Program Studi / Unit Kerja.');
        }
    }

    /**
     * Kirim notifikasi database ke seluruh akun Risk Owner (Prodi/Unit) risiko ini.
     */
    private function notifyOwnerRevision(RiskRegister $riskRegister, string $note): void
    {
        $owners = User::query()->where(function ($q) use ($riskRegister) {
            $hasCondition = false;

            if ($riskRegister->academic_program_id) {
                $hasCondition = true;
                $q->where('academic_program_id', $riskRegister->academic_program_id)
                    ->whereHas('roles', fn ($r) => $r->where('name', 'prodi'));
            }

            if ($riskRegister->unit_id) {
                if ($hasCondition) {
                    $q->orWhere(fn ($q2) => $q2->where('unit_id', $riskRegister->unit_id)
                        ->whereHas('roles', fn ($r) => $r->where('name', 'unit')));
                } else {
                    $q->where('unit_id', $riskRegister->unit_id)
                        ->whereHas('roles', fn ($r) => $r->where('name', 'unit'));
                }
            }
        })->get();

        if ($owners->isEmpty()) {
            return;
        }

        Notification::send($owners, new RiskRevisionRequested($riskRegister, $note, auth()->user()->name));
    }
}
