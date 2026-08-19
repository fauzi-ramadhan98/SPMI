<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\News;

class HomeController extends Controller
{
    public function index()
    {
        // Get sambutan kepala for home page sections if needed
        $sambutan = Page::where('slug', 'sambutan-kepala')->where('is_published', true)->first();
        $latestNews = News::where('is_published', true)->orderBy('published_at', 'desc')->take(3)->get();
        return view('public.home', compact('sambutan', 'latestNews'));
    }

    public function sambutan()
    {
        return view('public.sambutan');
    }

    public function profil()
    {
        $page = Page::where('slug', 'profil-lpm')->where('is_published', true)->firstOrFail();
        return view('public.page', compact('page'));
    }

    public function page($slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('public.page', compact('page'));
    }

    public function generic($slug)
    {
        // Check if a specific static blade exists using direct file check (supports hyphens in filenames)
        $viewPath = resource_path('views/public/' . $slug . '.blade.php');
        
        if (file_exists($viewPath)) {
            // Build view name: replace hyphens with dots for sub-dirs but handle hyphens in file names
            // We'll use view()->file() for direct blade file loading
            return view()->file($viewPath);
        }
        
        // Otherwise use the generic static template (fallback)
        $title = ucwords(str_replace('-', ' ', $slug));
        return view('public.generic', compact('title', 'slug'));
    }
}
