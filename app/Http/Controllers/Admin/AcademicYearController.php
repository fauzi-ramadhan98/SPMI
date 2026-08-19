<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::orderByDesc('name')->paginate(10);
        return view('admin.academic_years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic_years.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:10|unique:academic_years,name',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Tahun Akademik wajib diisi.',
            'name.unique'   => 'Tahun Akademik tersebut sudah terdaftar.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['created_by'] = auth()->id();

        AcademicYear::create($validated);

        return redirect()->route('admin.academic_years.index')
            ->with('success', 'Tahun Akademik berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:10', Rule::unique('academic_years', 'name')->ignore($academicYear->id)],
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Tahun Akademik wajib diisi.',
            'name.unique'   => 'Tahun Akademik tersebut sudah terdaftar.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $academicYear->update($validated);

        return redirect()->route('admin.academic_years.index')
            ->with('success', 'Tahun Akademik berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();
        return redirect()->route('admin.academic_years.index')
            ->with('success', 'Tahun Akademik berhasil dihapus.');
    }
}
