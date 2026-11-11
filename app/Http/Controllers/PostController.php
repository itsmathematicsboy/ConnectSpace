<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Home / Feed: tampilkan semua postingan (desc by created_at).
     */
    public function index(): View
    {
        $posts = Post::with([
            'user:id,name,avatar',
            'likes:user_id,post_id',
            'comments.user:id,name,avatar',
        ])
            ->withCount('likes')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('home', compact('posts'));
    }

    /**
     * Buat postingan baru (text, image, atau keduanya).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ], [
            'content.max' => 'Konten teks maksimal 5000 karakter.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max' => 'Ukuran gambar maksimal 4MB.',
        ]);

        // Validasi: minimal salah satu (content atau image) harus ada
        if (empty(trim($validated['content'] ?? '')) && ! $request->hasFile('image')) {
            return back()->withErrors(['content' => 'Postingan harus memiliki minimal teks atau gambar.'])->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => ! empty(trim($validated['content'] ?? '')) ? trim($validated['content']) : null,
            'image' => $imagePath,
        ]);

        return back()->with('success', 'Postingan berhasil dipublikasikan.');
    }

    /**
     * Hapus postingan milik sendiri.
     * - Verifikasi kepemilikan di server
     * - Cascade menghapus likes & comments (di level DB)
     * - Hapus file gambar terkait
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        if ($post->user_id !== Auth::id()) {
            return back()->with('error', 'Kamu tidak punya izin untuk menghapus postingan ini.');
        }

        // Hapus file gambar post
        if ($post->image && ! filter_var($post->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($post->image);
        }

        // Hapus file gambar pada komentar terkait
        foreach ($post->comments as $comment) {
            if ($comment->image && ! filter_var($comment->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($comment->image);
            }
        }

        // Cascade delete akan menghapus likes & comments secara otomatis
        $post->delete();

        return back()->with('success', 'Postingan berhasil dihapus.');
    }
}
