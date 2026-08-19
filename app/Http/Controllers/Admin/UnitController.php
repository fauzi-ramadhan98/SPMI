<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('users')->orderBy('category')->orderBy('name')->paginate(15);
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:units,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:administratif_umum,layanan_akademik,penunjang',
            'head_name' => 'nullable|string|max:255',
            'head_nidn' => 'nullable|string|max:255',
        ]);

        Unit::create([
            'code' => $request->code,
            'name' => $request->name,
            'category' => $request->category,
            'head_name' => $request->head_name,
            'head_nidn' => $request->head_nidn,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.units.index')->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:units,code,' . $unit->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:administratif_umum,layanan_akademik,penunjang',
            'head_name' => 'nullable|string|max:255',
            'head_nidn' => 'nullable|string|max:255',
        ]);

        $unit->update([
            'code' => $request->code,
            'name' => $request->name,
            'category' => $request->category,
            'head_name' => $request->head_name,
            'head_nidn' => $request->head_nidn,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.units.index')->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->users()->count() > 0 || $unit->auditAssignments()->count() > 0 || $unit->riskRegisters()->count() > 0) {
            return back()->with('error', 'Unit tidak dapat dihapus karena masih memiliki user, penugasan audit, atau profil risiko.');
        }

        $unit->delete();
        return redirect()->route('admin.units.index')->with('success', 'Unit kerja berhasil dihapus.');
    }
}
