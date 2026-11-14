<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentLikeController extends Controller
{
    /**
     * Toggle like/unlike komentar.
     * Bekerja untuk comment maupun reply (sama-sama instance Comment).
     */
    public function toggle(Request $request, Comment $comment): RedirectResponse
    {
        $user = Auth::user();

        $existing = CommentLike::query()
            ->where('user_id', $user->id)
            ->where('comment_id', $comment->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            try {
                CommentLike::create([
                    'user_id'    => $user->id,
                    'comment_id' => $comment->id,
                ]);
                $liked = true;
            } catch (\Illuminate\Database\QueryException $e) {
                // UNIQUE constraint sbg safety net kalau ada race condition
                $liked = true;
            }
        }

        return back()->with('success', $liked ? 'Kamu menyukai komentar ini.' : 'Like dibatalkan.');
    }
}
