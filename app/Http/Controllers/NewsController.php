<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::where('is_published', true)
                    ->orderBy('published_at', 'desc')
                    ->paginate(9);
        return view('public.news.index', compact('news'));
    }

    public function show($slug)
    {
        $news = News::where('slug', $slug)->where('is_published', true)->firstOrFail();
        return view('public.news.show', compact('news'));
    }
}
