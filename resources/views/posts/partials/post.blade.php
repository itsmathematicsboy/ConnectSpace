@php
    $isLiked = $post->isLikedBy(Auth::user());
    $totalLikes = $post->likes_count ?? $post->likes->count();
    $isOwner = $post->user_id === Auth::id();
@endphp

<div class="card-friendster mb-3 feed-post" id="post-{{ $post->id }}">
    {{-- Header --}}
    <div class="p-3 d-flex align-items-center gap-3">
        <img src="{{ $post->user->avatarUrl() }}" alt="{{ $post->user->name }}" class="avatar avatar-md" width="44" height="44">
        <div class="flex-grow-1">
            <div class="fw-semibold">{{ $post->user->name }}</div>
            <div class="text-muted-2 small">
                <i class="bi bi-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                &middot; {{ $post->created_at->format('d M Y, H:i') }}
            </div>
        </div>
        @if ($isOwner)
            <form method="POST" action="{{ route('posts.destroy', $post) }}"
                  onsubmit="return confirm('Hapus postingan ini? Like & komentar terkait juga akan dihapus.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus postingan" aria-label="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif
    </div>

    {{-- Content --}}
    @if ($post->content)
        <div class="px-3 pb-3">
            <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ $post->content }}</p>
        </div>
    @endif

    {{-- Image --}}
    @if ($post->image)
        <div class="border-top border-bottom" style="background:#f3f4f6;">
            <img src="{{ $post->imageUrl() }}" alt="gambar-postingan" class="post-image" loading="lazy">
        </div>
    @endif

    {{-- Actions Bar --}}
    <div class="p-3 d-flex align-items-center gap-2">
        <form method="POST" action="{{ route('posts.like', $post) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm like-btn {{ $isLiked ? 'liked' : '' }}" aria-pressed="{{ $isLiked ? 'true' : 'false' }}">
                <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                <span class="ms-1">{{ $isLiked ? 'Disukai' : 'Suka' }}</span>
                <span class="badge bg-light text-dark rounded-pill ms-1">{{ $totalLikes }}</span>
            </button>
        </form>
        <button type="button" class="btn btn-sm text-muted-2" data-bs-toggle="collapse" data-bs-target="#comments-{{ $post->id }}" aria-expanded="true">
            <i class="bi bi-chat"></i>
            <span class="ms-1">Komentar</span>
            @if($post->comments->isNotEmpty())
                <span class="badge bg-light text-dark rounded-pill ms-1">{{ $post->comments->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Comments --}}
    <div class="collapse show" id="comments-{{ $post->id }}">
        <div class="px-3 pb-3">
            @if ($post->comments->isNotEmpty())
                <div class="comment-list">
                    @foreach ($post->comments as $comment)
                        @include('posts.partials.comment', ['comment' => $comment])
                    @endforeach
                </div>
            @endif

            {{-- Comment Form --}}
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="d-flex align-items-start gap-2">
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="me" class="avatar avatar-sm" width="36" height="36">
                    <div class="flex-grow-1">
                        <textarea name="comment" rows="1" class="form-control form-control-sm {{ $errors->has('comment') ? 'is-invalid' : '' }}"
                                  placeholder="Tulis komentar..."></textarea>
                        @error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <label class="btn btn-light btn-sm mb-0" style="border:1px solid var(--fs-border); cursor:pointer;">
                                <i class="bi bi-image"></i>
                                <input type="file" name="image" accept="image/*" class="d-none">
                            </label>
                            <button type="submit" class="btn btn-friendster btn-sm">
                                <i class="bi bi-send"></i> Kirim
                            </button>
                        </div>
                        @error('image')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>