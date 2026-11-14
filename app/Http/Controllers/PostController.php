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
        'comments' => fn ($q) => $q->whereNull('parent_id')
                                   ->with([
                                       'replies.user:id,name,avatar',
                                       'replies.likes:user_id,comment_id',
                                       'likes:user_id,comment_id',
                                   ])
                                   ->orderBy('created_at', 'asc'),
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
            'content' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'location'  => ['nullable', 'string', 'max:255'],                          // ← BARU
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],                  // ← BARU
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],                // ← BARU
        ], [
        ], [
            'content.max' => 'Konten teks maksimal 500 karakter.',
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
            'location'  => ! empty(trim($validated['location'] ?? '')) ? trim($validated['location']) : null,        // ← BARU
            'latitude'  => $validated['latitude'] ?? null,                                                            // ← BARU
            'longitude' => $validated['longitude'] ?? null,  
        ]);

        return back()->with('success', 'Postingan berhasil dipublikasikan.');
    }

    /**
     * Update postingan milik sendiri (text, image, atau keduanya).
     * Verifikasi kepemilikan dilakukan di server.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        // Otorisasi: hanya pemilik yang boleh edit
        if ($post->user_id !== Auth::id()) {
            return back()->with('error', 'Kamu tidak punya izin untuk mengedit postingan ini.');
        }

        $validated = $request->validate([
            'content'       => ['nullable', 'string', 'max:500'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_image'  => ['nullable', 'boolean'],
            'location'      => ['nullable', 'string', 'max:255'],                       // ← BARU
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],               // ← BARU
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'], 
        ], [
            'content.max'     => 'Konten teks maksimal 500 karakter.',
            'image.image'     => 'File harus berupa gambar.',
            'image.mimes'     => 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max'       => 'Ukuran gambar maksimal 4MB.',
        ]);

        $hasNewImage       = $request->hasFile('image') && $request->file('image')->isValid();
        $removeCurrent     = $request->boolean('remove_image');
        $willHaveImage     = $hasNewImage || ($post->image && ! $removeCurrent);
        $hasContent        = ! empty(trim($validated['content'] ?? ''));

        // Validasi: minimal salah satu (content atau image) harus tetap ada setelah edit
        if (! $hasContent && ! $willHaveImage) {
            return back()
                ->withErrors(['content' => 'Postingan harus memiliki minimal teks atau gambar.'])
                ->withInput();
        }

        $post->content = $hasContent ? trim($validated['content']) : null;

        // Handle gambar
        if ($hasNewImage) {
            // Hapus gambar lama (jika lokal)
            if ($post->image && ! filter_var($post->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = $request->file('image')->store('posts', 'public');
        } elseif ($removeCurrent) {
            if ($post->image && ! filter_var($post->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($post->image);
            }
            $post->image = null;
        }

        $post->save();
        $post->location = !empty(trim($validated['location'] ?? '')) ? trim($validated['location']) : null;
        $post->latitude = $validated['location'] ?? null;
        $post->longitude = $validated['longitude'] ?? null;

        return back()->with('success', 'Postingan berhasil diperbarui.');
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
