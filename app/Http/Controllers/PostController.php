<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index() {}

    public function create()
    {
        return Inertia::render('CreatePost');
    }

    public function store(Request $request)
    {

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'], // 5 MB
        ]);

        $path = $request->file('image')->store('blog', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }
}
