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
 * Tambah komentar atau reply pada postingan.
 * - Kalau parent_id kosong → comment top-level
 * - Kalau parent_id diisi → reply dari comment induk
 */
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'comment'   => ['nullable', 'string', 'max:2000'],
            'image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'location'  => ['nullable', 'string', 'max:255'],
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ], [
            'comment.max'   => 'Komentar maksimal 2000 karakter.',
            'image.image'   => 'File harus berupa gambar.',
            'image.mimes'   => 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max'     => 'Ukuran gambar maksimal 4MB.',
            'parent_id.exists' => 'Komentar induk tidak ditemukan.',
        ]);

        // Validasi: minimal salah satu (teks atau gambar) harus ada
        if (empty(trim($validated['comment'] ?? '')) && ! $request->hasFile('image')) {
            return back()->withErrors(['comment' => 'Komentar harus memiliki minimal teks atau gambar.'])->withInput();
        }

        // Validasi parent_id: kalau diisi, pastikan milik post yang sama
        if (! empty($validated['parent_id'])) {
            $parent = Comment::find($validated['parent_id']);
            if (! $parent || $parent->post_id !== $post->id) {
                return back()->with('error', 'Komentar induk tidak valid untuk postingan ini.')->withInput();
            }
        }

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('comments', 'public');
        }

        Comment::create([
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'comment'   => ! empty(trim($validated['comment'] ?? '')) ? trim($validated['comment']) : null,
            'image'     => $imagePath,
            'location'  => ! empty(trim($validated['location'] ?? '')) ? trim($validated['location']) : null,
            'latitude'  => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ]);

        return back()->with('success', empty($validated['parent_id']) ? 'Komentar berhasil ditambahkan.' : 'Balasan berhasil ditambahkan.');
    }

        /**
     * Update komentar milik sendiri.
     * Verifikasi: hanya pemilik komentar yang boleh edit.
     */
    public function update(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        // Otorisasi: hanya pemilik komentar
        if ($comment->user_id !== Auth::id()) {
            return back()->with('error', 'Kamu tidak punya izin untuk mengedit komentar ini.');
        }

        // Pastikan komentar benar-benar milik post ini
        if ($comment->post_id !== $post->id) {
            return back()->with('error', 'Komentar tidak valid.');
        }

        $validated = $request->validate([
            'comment'      => ['nullable', 'string', 'max:2000'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'location'     => ['nullable', 'string', 'max:255'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'comment.max' => 'Komentar maksimal 2000 karakter.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF.',
            'image.max'   => 'Ukuran gambar maksimal 4MB.',
        ]);

        $hasNewImage   = $request->hasFile('image') && $request->file('image')->isValid();
        $removeCurrent = $request->boolean('remove_image');
        $willHaveImage = $hasNewImage || ($comment->image && ! $removeCurrent);
        $hasContent    = ! empty(trim($validated['comment'] ?? ''));

        // Validasi: minimal salah satu harus tetap ada
        if (! $hasContent && ! $willHaveImage) {
            return back()
                ->withErrors(['comment' => 'Komentar harus memiliki minimal teks atau gambar.'])
                ->withInput();
        }

        $comment->comment = $hasContent ? trim($validated['comment']) : null;

        if ($hasNewImage) {
            if ($comment->image && ! filter_var($comment->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($comment->image);
            }
            $comment->image = $request->file('image')->store('comments', 'public');
        } elseif ($removeCurrent) {
            if ($comment->image && ! filter_var($comment->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($comment->image);
            }
            $comment->image = null;
        }

        $comment->save();
        $comment->location  = ! empty(trim($validated['location'] ?? '')) ? trim($validated['location']) : null;
        $comment->latitude  = $validated['latitude'] ?? null;
        $comment->longitude = $validated['longitude'] ?? null;

        return back()->with('success', 'Komentar berhasil diperbarui.');
    }

    /**
     * Hapus komentar atau balasan milik sendiri.
     * - Verifikasi kepemilikan di server
     * - Hapus file gambar dari storage (jika ada)
     * - Kalau hapus comment parent → semua reply + like reply + like comment ikut terhapus via cascadeOnDelete
     * - Kalau hapus reply → hanya reply itu + like-nya yang terhapus
     */
    public function destroy(Request $request, Post $post, Comment $comment): RedirectResponse
    {
        // Otorisasi: hanya pemilik komentar yang boleh hapus
        if ($comment->user_id !== Auth::id()) {
            return back()->with('error', 'Kamu tidak punya izin untuk menghapus komentar ini.');
        }

        // Pastikan komentar benar-benar milik post ini (anti tampering)
        if ($comment->post_id !== $post->id) {
            return back()->with('error', 'Komentar tidak valid.');
        }

        // Hapus file gambar komentar dari storage (jika lokal)
        if ($comment->image && ! filter_var($comment->image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($comment->image);
        }

        // Kalau ini adalah comment parent, hapus juga file gambar dari semua reply
        // (cascade DB akan hapus record reply, tapi file fisik perlu dihapus manual)
        if (is_null($comment->parent_id)) {
            foreach ($comment->replies as $reply) {
                if ($reply->image && ! filter_var($reply->image, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($reply->image);
                }
            }
        }

        // Cascade delete akan handle:
        // - Reply (via FK parent_id cascadeOnDelete)
        // - Comment likes (via FK comment_id cascadeOnDelete)
        // - Comment likes dari reply juga ikut terhapus (cascade dari reply)
        $comment->delete();

        $msg = is_null($comment->parent_id)
            ? 'Komentar berhasil dihapus (beserta balasannya).'
            : 'Balasan berhasil dihapus.';

        return back()->with('success', $msg);
    }
}
