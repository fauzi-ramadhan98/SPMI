<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;

class AcademicProgramController extends Controller
{
    public function index()
    {
        $programs = AcademicProgram::orderBy('degree_level')->orderBy('name')->get();
        return view('admin.academic_programs.index', compact('programs'));
    }

    public function edit(AcademicProgram $academicProgram)
    {
        return view('admin.academic_programs.edit', compact('academicProgram'));
    }

    public function update(Request $request, AcademicProgram $academicProgram)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'faculty'        => 'nullable|string|max:255',
            'degree_level'   => 'required|in:D3,S1,S2,S3,Profesi',
            'accreditation'  => 'nullable|string|max:50',
            'head_name'      => 'nullable|string|max:255',
            'head_nidn'      => 'nullable|string|max:50',
        ]);

        // Checkbox sends "on" when checked, absent when unchecked — not a Laravel boolean
        $validated['is_active'] = $request->has('is_active');
        $academicProgram->update($validated);

        return redirect()->route('admin.academic_programs.index')
            ->with('success', 'Program Studi berhasil diperbarui.');
    }
}
