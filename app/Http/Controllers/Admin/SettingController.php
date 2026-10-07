<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\DocumentCodeGenerator;
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
            // Pengaturan cetak Laporan AMI (PDF)
            'report_kode' => 'nullable|string|max:120',
            'report_edisi' => 'nullable|string|max:30',
            'report_cover_enabled' => 'nullable|in:0,1',
            'report_cover_image' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            // Pengaturan kode dokumen SPMI
            'document_code_prefix' => 'nullable|string|max:120',
            'document_code_format' => 'nullable|string|max:255',
            'document_auto_generate' => 'nullable|in:0,1',
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

        // ===== Pengaturan cetak Laporan AMI (header + cover halaman pertama) =====
        if ($request->has('report_kode')) {
            Setting::set(
                'report_kode',
                trim((string) $request->input('report_kode')) ?: 'STMIKMI.LPMI.AMI.VIII.1',
                'laporan',
                'Kode Dokumen Laporan AMI',
                $userId
            );
        }
        if ($request->has('report_edisi')) {
            Setting::set(
                'report_edisi',
                trim((string) $request->input('report_edisi')) ?: '2',
                'laporan',
                'Edisi Dokumen Laporan AMI',
                $userId
            );
        }
        if ($request->has('report_cover_enabled')) {
            Setting::set(
                'report_cover_enabled',
                $request->boolean('report_cover_enabled') ? '1' : '0',
                'laporan',
                'Tampilkan Cover Laporan',
                $userId
            );
        }
        if ($request->boolean('report_cover_image_delete')) {
            $old = (string) setting('report_cover_image');
            if ($old !== '' && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('report_cover_image', null, 'laporan', 'Cover Kustom Laporan', $userId);
        }
        if ($request->hasFile('report_cover_image')) {
            $file = $request->file('report_cover_image');
            $path = $file->storeAs(
                'images',
                'report-cover-' . time() . '.' . $file->getClientOriginalExtension(),
                'public'
            );
            $old = (string) setting('report_cover_image');
            if ($old !== '' && $old !== $path && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            Setting::set('report_cover_image', $path, 'laporan', 'Cover Kustom Laporan', $userId);
        }

        // ===== Pengaturan kode dokumen SPMI (prefix, format template, auto-generate) =====
        if ($request->has('document_code_prefix')) {
            Setting::set(
                'document_code_prefix',
                trim((string) $request->input('document_code_prefix')) ?: 'STMIK-MI/SPMI',
                'dokumen',
                'Prefix Kode Dokumen SPMI (misal: STMIK-MI/SPMI)',
                $userId
            );
        }
        if ($request->has('document_code_format')) {
            $format = trim((string) $request->input('document_code_format')) ?: '{prefix}/{parent_code}.{child_code}.{seq}';
            $validation = DocumentCodeGenerator::validateTemplate($format);
            if (!$validation['valid']) {
                return back()->withErrors(['document_code_format' => $validation['message']])->withInput();
            }
            Setting::set('document_code_format', $format, 'dokumen', 'Format/Template Kode Dokumen', $userId);
        }
        if ($request->has('document_auto_generate')) {
            Setting::set(
                'document_auto_generate',
                $request->boolean('document_auto_generate') ? '1' : '0',
                'dokumen',
                'Generate Kode Dokumen Otomatis',
                $userId
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Konfigurasi aplikasi berhasil disimpan.');
    }
}