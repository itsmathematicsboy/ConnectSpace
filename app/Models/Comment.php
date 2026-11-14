<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; 

#[Fillable(['post_id', 'user_id', 'parent_id', 'comment', 'image',  'location', 'latitude', 'longitude'])]
class Comment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Comment induk (null kalau ini adalah comment top-level).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Reply dari comment ini (1 level only).
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    /**
     * Cek apakah komentar punya data lokasi.
     */
    public function hasLocation(): bool
    {
        return ! empty($this->location) || ($this->latitude && $this->longitude);
    }

    /**
     * URL Google Maps untuk koordinat komentar (jika ada).
     */
    public function mapsUrl(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        if ($this->location) {
            return 'https://www.google.com/maps?q='.urlencode($this->location);
        }

        return null;
    }

    /**
     * Like pada komentar ini.
    */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Cek apakah user tertentu sudah like komentar ini.
    */
    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
