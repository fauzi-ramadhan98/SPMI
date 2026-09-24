<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\ChecklistItem;
use App\Models\QualityStandard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChecklistItemController extends Controller
{
    public function index()
    {
        $standards = QualityStandard::with(['checklistItems', 'document.decree'])
            ->orderBy('kode_standar')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $standardOptions = QualityStandard::orderBy('kode_standar')->get();

        $standardIndicators = $standardOptions->map(function ($std) {
            $indicators = [];
            foreach ($std->ikuIndicators() as $i => $r) {
                $indicators[] = ['key' => 'IKU ' . ($i + 1), 'text' => $r['text'], 'target' => $r['target'] ?? ''];
            }
            foreach ($std->iktIndicators() as $i => $r) {
                $indicators[] = ['key' => 'IKT ' . ($i + 1), 'text' => $r['text'], 'target' => $r['target'] ?? ''];
            }

            return [
                'id'                => $std->id,
                'document_id'       => $std->document_id,
                'kode_standar'      => $std->kode_standar ?: '—',
                'pernyataan_standar'=> $std->pernyataan_standar ?: '',
                'name'              => $std->name ?: '',
                'indicators'        => $indicators,
            ];
        })->values();

        // Hanya document dokumen_mutu yang aktif
        $documents = Document::where('module', 'dokumen_mutu')
            ->where('status', 'aktif')
            ->with('decree')
            ->orderBy('code')
            ->get();

        return view('admin.checklist_items.index', compact('standards', 'standardOptions', 'standardIndicators', 'documents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quality_standard_id' => 'required|exists:quality_standards,id',
            'code'                => 'nullable|string|max:100',
            'indicator_key'       => 'nullable|string|max:50',
            'audit_question'      => 'nullable|string',
            'rubric_4'            => 'nullable|string',
            'rubric_3'            => 'nullable|string',
            'rubric_2'            => 'nullable|string',
            'rubric_1'            => 'nullable|string',
            'evidence_document'   => 'nullable|string|max:255',
            'indicator'           => 'nullable|string',
            'max_score'           => 'nullable|integer|min:1|max:100',
            'sort_order'          => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        if (empty($validated['max_score'])) $validated['max_score'] = 4;
        if (empty($validated['sort_order'])) $validated['sort_order'] = 0;

        $validated['indicator'] = $this->resolveIndicator($validated['quality_standard_id'], $validated['indicator_key'] ?? null)
            ?? ($validated['indicator'] ?? null);

        if (empty($validated['indicator'])) {
            return back()->withErrors(['indicator_key' => 'Pilih IKU / IKT terkait pada standar yang dipilih.'])->withInput();
        }

        ChecklistItem::create($validated);

        return back()->with('success', 'Butir daftar tilik berhasil ditambahkan.');
    }

    public function update(Request $request, ChecklistItem $checklistItem)
    {
        $validated = $request->validate([
            'indicator_key'     => 'nullable|string|max:50',
            'audit_question'    => 'nullable|string',
            'rubric_4'          => 'nullable|string',
            'rubric_3'          => 'nullable|string',
            'rubric_2'          => 'nullable|string',
            'rubric_1'          => 'nullable|string',
            'evidence_document' => 'nullable|string|max:255',
            'code'              => 'nullable|string|max:100',
            'max_score'         => 'nullable|integer|min:1|max:100',
            'sort_order'        => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        if (empty($validated['max_score'])) $validated['max_score'] = 4;
        if (empty($validated['sort_order'])) $validated['sort_order'] = 0;

        if (!empty($validated['indicator_key'])) {
            $resolved = $this->resolveIndicator($checklistItem->quality_standard_id, $validated['indicator_key']);
            if ($resolved !== null) {
                $validated['indicator'] = $resolved;
            }
        }

        $checklistItem->update($validated);

        return back()->with('success', 'Butir daftar tilik berhasil diperbarui.');
    }

    /**
     * Ambil teks indikator dari IKU/IKT standar mutu berdasarkan key (mis. "IKU 1").
     * Mengembalikan null bila key kosong atau tidak cocok.
     */
    private function resolveIndicator(int $qualityStandardId, ?string $key): ?string
    {
        if (!$key) {
            return null;
        }

        $standard = QualityStandard::find($qualityStandardId);
        if (!$standard) {
            return null;
        }

        $rows = [];
        foreach ($standard->ikuIndicators() as $i => $r) {
            $rows[] = ['key' => 'IKU ' . ($i + 1), 'text' => $r['text']];
        }
        foreach ($standard->iktIndicators() as $i => $r) {
            $rows[] = ['key' => 'IKT ' . ($i + 1), 'text' => $r['text']];
        }

        foreach ($rows as $row) {
            if (strcasecmp($row['key'], $key) === 0) {
                return $row['text'];
            }
        }

        return null;
    }

    public function destroy(ChecklistItem $checklistItem)
    {
        $checklistItem->delete();
        return back()->with('success', 'Butir daftar tilik berhasil dihapus.');
    }
}
