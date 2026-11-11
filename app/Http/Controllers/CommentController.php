<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    /**
     * Tambah komentar pada postingan.
     * Komentar boleh berisi teks, gambar, atau keduanya.
     */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ], [
            'comment.max' => 'Komentar maksimal 2000 karakter.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max' => 'Ukuran gambar maksimal 4MB.',
        ]);

        // Minimal salah satu (teks atau gambar) harus ada
        if (empty(trim($validated['comment'] ?? '')) && ! $request->hasFile('image')) {
            return back()->withErrors(['comment' => 'Komentar harus memiliki minimal teks atau gambar.'])->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('comments', 'public');
        }

        Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'comment' => ! empty(trim($validated['comment'] ?? '')) ? trim($validated['comment']) : null,
            'image' => $imagePath,
        ]);

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }
}
