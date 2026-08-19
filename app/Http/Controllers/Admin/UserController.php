<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'academicProgram', 'unit'])->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $academicPrograms = \App\Models\AcademicProgram::all();
        $units = \App\Models\Unit::all();
        return view('admin.users.create', compact('roles', 'academicPrograms', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'nidn' => 'nullable|string|max:20',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'unit_id' => 'nullable|exists:units,id',
            'pimpinan_level' => 'nullable|in:ketua,wakil',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nidn' => $request->role === 'auditor' ? $request->nidn : null,
            'academic_program_id' => $request->role === 'prodi' ? $request->academic_program_id : null,
            'unit_id' => $request->role === 'unit' ? $request->unit_id : null,
            'pimpinan_level' => $request->role === 'pimpinan' ? $request->pimpinan_level : null,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $academicPrograms = \App\Models\AcademicProgram::all();
        $units = \App\Models\Unit::all();
        return view('admin.users.edit', compact('user', 'roles', 'academicPrograms', 'units'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'role' => 'required|exists:roles,name',
            'nidn' => 'nullable|string|max:20',
            'academic_program_id' => 'nullable|exists:academic_programs,id',
            'unit_id' => 'nullable|exists:units,id',
            'pimpinan_level' => 'nullable|in:ketua,wakil',
        ];

        if ($request->email !== $user->email) {
            $rules['email'] = 'required|string|email|max:255|unique:users';
        }

        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->nidn = $request->role === 'auditor' ? $request->nidn : null;
        $user->academic_program_id = $request->role === 'prodi' ? $request->academic_program_id : null;
        $user->unit_id = $request->role === 'unit' ? $request->unit_id : null;
        $user->pimpinan_level = $request->role === 'pimpinan' ? $request->pimpinan_level : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
