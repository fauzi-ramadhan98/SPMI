<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('author')->paginate(10);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

        Page::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'meta_description' => $request->meta_description,
            'is_published' => $request->has('is_published'),
            'author_id' => auth()->id(),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dibuat.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

        $page->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'meta_description' => $request->meta_description,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    public function destroy(Page $page)
    {
        // Don't allow deleting specific essential pages
        if (in_array($page->slug, ['sambutan-kepala', 'profil-lpm'])) {
            return back()->with('error', 'Halaman inti ini tidak boleh dihapus.');
        }

        $page->delete();
        return redirect()->route('admin.pages.index')->with('success', 'Halaman berhasil dihapus.');
    }
}
