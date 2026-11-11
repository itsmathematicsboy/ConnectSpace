<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Toggle like/unlike.
     * - Klik pertama → like
     * - Klik kedua → unlike
     * - Mencegah duplikasi lewat UNIQUE constraint + firstOrCreate
     */
    public function toggle(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $wantsJson = $request->expectsJson();

        if (! $user) {
            return $wantsJson
                ? response()->json(['error' => 'Unauthenticated.'], 401)
                : redirect()->route('login');
        }

        $existing = Like::query()
            ->where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->first();

        if ($existing) {
            // Unlike
            $existing->delete();
            $liked = false;
        } else {
            // Like (dengan unique constraint sbg safety net)
            try {
                Like::create([
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                ]);
                $liked = true;
            } catch (\Illuminate\Database\QueryException $e) {
                // Karena ada UNIQUE constraint, duplikasi tetap ditolak DB
                $liked = true;
            }
        }

        $totalLikes = (int) $post->likes()->count();

        if ($wantsJson) {
            return response()->json([
                'liked' => $liked,
                'total_likes' => $totalLikes,
            ]);
        }

        return back()->with('success', $liked ? 'Kamu menyukai postingan ini.' : 'Like dibatalkan.');
    }
}
