<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_short_name' => 'nullable|string|max:100',
            'academic_year' => 'nullable|string|max:20',
            'semester' => 'nullable|in:Ganjil,Genap',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $userId = Auth::id();
        $inputs = $request->only([
            'institution_name', 'institution_short_name', 'academic_year',
            'semester', 'address', 'phone', 'email',
        ]);

        foreach ($inputs as $key => $value) {
            Setting::set($key, $value, 'general', null, $userId);
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo.' . $file->getClientOriginalExtension();
            $file->storeAs('public/images', $filename, 'public');
            Setting::set('logo', 'images/' . $filename, 'general', null, $userId);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Konfigurasi aplikasi berhasil disimpan.');
    }
}