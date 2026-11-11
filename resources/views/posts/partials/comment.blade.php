<div class="comment-item d-flex align-items-start gap-2">
    <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->user->name }}" class="avatar avatar-sm" width="36" height="36">
    <div class="flex-grow-1">
        <div class="bg-light rounded-3 px-3 py-2 d-inline-block" style="max-width:100%;">
            <div class="fw-semibold small">{{ $comment->user->name }}</div>
            @if ($comment->comment)
                <div class="small" style="white-space: pre-wrap; word-wrap: break-word;">{{ $comment->comment }}</div>
            @endif
        </div>
        @if ($comment->image)
            <div class="mt-2">
                <img src="{{ $comment->imageUrl() }}" alt="gambar-komentar" class="feed-comment-image border" loading="lazy">
            </div>
        @endif
        <div class="text-muted-2 mt-1" style="font-size:.75rem;">
            {{ $comment->created_at->diffForHumans() }}
            &middot; {{ $comment->created_at->format('d M Y, H:i') }}
        </div>
    </div>
</div>