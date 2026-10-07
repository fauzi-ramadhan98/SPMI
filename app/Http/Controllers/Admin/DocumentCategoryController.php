<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;

class DocumentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $module = $request->get('module', 'dokumen_mutu');
        $validModules = ['dokumen_mutu', 'surat_tugas', 'rtm', 'evaluasi_diri', 'rtl', 'lainnya'];
        
        if (!in_array($module, $validModules)) {
            $module = 'dokumen_mutu';
        }
        
        $parentCategories = DocumentCategory::where('module', $module)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->paginate(15);
        
        // Muat sub-kategori secara rekursif (anak, cucu, dst) untuk setiap kategori root
        // yang tampil di halaman ini, agar mendukung kedalaman hierarki tak terbatas.
        foreach ($parentCategories as $category) {
            $category->loadChildrenRecursively();
        }
        
        return view('admin.document-categories.index', compact('module', 'parentCategories', 'validModules'));
    }
    
    public function create(Request $request)
    {
        $module = $request->get('module', 'dokumen_mutu');
        $validModules = ['dokumen_mutu', 'surat_tugas', 'rtm', 'evaluasi_diri', 'rtl', 'lainnya'];
        
        if (!in_array($module, $validModules)) {
            $module = 'dokumen_mutu';
        }
        
        // Gunakan flatTreeForModule() untuk menampilkan SEMUA level, bukan hanya root
        $parentCategories = DocumentCategory::flatTreeForModule($module);
        
        return view('admin.document-categories.create', compact('module', 'parentCategories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_categories,code',
            'module' => 'required|in:dokumen_mutu,surat_tugas,rtm,evaluasi_diri,rtl,lainnya',
            'parent_id' => 'nullable|exists:document_categories,id',
            'target_roles' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($request->parent_id) {
            $parent = DocumentCategory::find($request->parent_id);
            if ($parent && $parent->module !== $request->module) {
                return back()->withErrors([
                    'parent_id' => 'Kategori induk harus berasal dari modul yang sama.'
                ]);
            }
        }
        
        DocumentCategory::create([
            'name' => $request->name,
            'code' => $request->code,
            'module' => $request->module,
            'parent_id' => $request->parent_id,
            'target_roles' => $request->target_roles,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('admin.document-categories.index', ['module' => $request->module])
            ->with('success', 'Kategori dokumen berhasil ditambahkan.');
    }
    
    public function edit(DocumentCategory $documentCategory)
    {
        $module = $documentCategory->module;
        // Exclude diri sendiri + semua descendant-nya (mencegah circular reference)
        $excludeIds = array_merge(
            [$documentCategory->id],
            $documentCategory->descendantIds()
        );
        $parentCategories = DocumentCategory::flatTreeForModule($module, $excludeIds);
        
        return view('admin.document-categories.edit', compact('documentCategory', 'module', 'parentCategories'));
    }
    
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:document_categories,code,' . $documentCategory->id,
            'parent_id' => 'nullable|exists:document_categories,id',
            'target_roles' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($request->parent_id) {
            $parent = DocumentCategory::find($request->parent_id);
            if ($parent && $parent->module !== $documentCategory->module) {
                return back()->withErrors([
                    'parent_id' => 'Kategori induk harus berasal dari modul yang sama.'
                ]);
            }
            // Cegah circular reference: parent_id tidak boleh sama dengan kategori ini sendiri,
            // atau salah satu descendant-nya (agar tidak terjadi loop A->B->C->A)
            if ($parent && $parent->id === $documentCategory->id) {
                return back()->withErrors([
                    'parent_id' => 'Kategori tidak dapat menjadi parent dari dirinya sendiri.'
                ]);
            }
            if ($parent && in_array($parent->id, $documentCategory->descendantIds(), true)) {
                return back()->withErrors([
                    'parent_id' => 'Kategori induk tidak boleh berada di bawah kategori ini (akan terjadi hierarki berulang).'
                ]);
            }
        }
        
        $documentCategory->update([
            'name' => $request->name,
            'code' => $request->code,
            'parent_id' => $request->parent_id,
            'target_roles' => $request->target_roles,
            'is_active' => $request->has('is_active'),
        ]);
        
        return redirect()->route('admin.document-categories.index', ['module' => $documentCategory->module])
            ->with('success', 'Kategori dokumen berhasil diperbarui.');
    }
    
    public function destroy(DocumentCategory $documentCategory)
    {
        $module = $documentCategory->module;
        
        if ($documentCategory->documents()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih ada dokumen yang menggunakannya.');
        }
        
        if ($documentCategory->children()->count() > 0) {
            return back()->with('error', 'Kategori induk tidak dapat dihapus karena masih punya kategori anak.');
        }
        
        $documentCategory->delete();
        
        return redirect()->route('admin.document-categories.index', ['module' => $module])
            ->with('success', 'Kategori dokumen berhasil dihapus.');
    }
}
