<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class PublicDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::where('is_public', true);

        if ($request->filled('type')) {
            $query->where('document_type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $types = Document::where('is_public', true)->select('document_type')->distinct()->pluck('document_type');

        return view('public.documents.index', compact('documents', 'types'));
    }

    public function download($id)
    {
        $document = Document::where('is_public', true)->findOrFail($id);
        
        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }
}
