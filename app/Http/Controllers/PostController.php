<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function create()
    {
        return view('posts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'nullable|string',
            'images.*' => 'nullable|image|max:10240', // 10MB
        ]);

        $post = Post::create($request->only('title', 'body'));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $post->addMedia($file)
                     ->preservingOriginal()
                     ->toMediaCollection('images');
            }
        }

        return response()->json([
            'redirect' => route('posts.show', $post)
        ]);

        // return redirect()->route('posts.show', $post)
        //                  ->with('status', 'Post created, images queued for processing.');
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}
