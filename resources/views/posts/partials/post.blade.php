@php
    $isLiked = $post->isLikedBy(Auth::user());
    $totalLikes = $post->likes_count ?? $post->likes->count();
    $isOwner = $post->user_id === Auth::id();
@endphp

<div class="card-friendster mb-3 feed-post" id="post-{{ $post->id }}">
    {{-- Header --}}
    <div class="p-3 d-flex align-items-center gap-3">
        <a href="{{ route('profile.show', $post->user) }}" class="text-decoration-none d-flex align-items-center gap-3 flex-grow-1">
        <img src="{{ $post->user->avatarUrl() }}" alt="{{ $post->user->name }}" class="avatar avatar-md" width="44" height="44">
        <div>
            <div class="fw-semibold text-dark">{{ $post->user->name }}</div>
            <div class="text-muted-2 small">
                <i class="bi bi-clock me-1"></i>{{ $post->created_at->diffForHumans() }}
                &middot; {{ $post->created_at->format('d M Y, H:i') }}
            </div>
        </div>
        </a>
        @if ($isOwner)
        <div class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#editPostModal-{{ $post->id }}"
                    title="Edit postingan" aria-label="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <form method="POST" action="{{ route('posts.destroy', $post) }}"
                onsubmit="return confirm('Hapus postingan ini? Like & komentar terkait juga akan dihapus.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus postingan" aria-label="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
            {{-- Modal Edit Post --}}
            <div class="modal fade" id="editPostModal-{{ $post->id }}" tabindex="-1"
                aria-labelledby="editPostModalLabel-{{ $post->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('posts.update', $post) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editPostModalLabel-{{ $post->id }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit Postingan
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Lokasi</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" name="location"
                                            class="form-control"
                                            value="{{ old('location', $post->location) }}"
                                            placeholder="Lokasi (opsional)">
                                        <button type="button" class="btn btn-outline-friendster" onclick="detectLocation(this)">
                                            <i class="bi bi-crosshair"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="latitude" value="{{ old('latitude', $post->latitude) }}">
                                    <input type="hidden" name="longitude" value="{{ old('longitude', $post->longitude) }}">
                                    <div class="form-text">Klik ikon crosshair untuk deteksi otomatis.</div>
                                </div>

                                @if ($post->image)
                                    <div class="mb-3">
                                        <label class="form-label fw-medium small">Gambar saat ini:</label><br>
                                        <img src="{{ $post->imageUrl() }}" alt="gambar-saat-ini"
                                            style="max-height:180px;border-radius:.5rem;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox"
                                                name="remove_image" value="1" id="rm-img-{{ $post->id }}">
                                            <label class="form-check-label small text-danger" for="rm-img-{{ $post->id }}">
                                                Hapus gambar ini
                                            </label>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-medium">Ganti Gambar (opsional)</label>
                                    <input type="file" name="image" class="form-control" accept="image/*">
                                    <div class="form-text">Format: JPG, JPEG, PNG, WEBP, GIF. Maks 4MB.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" id="editPostSubmitBtn-{{$post->id}}" class="btn btn-friendster">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Content --}}
    @if ($post->content)
        <div class="px-3 pb-3">
            <p class="mb-0" style="white-space: pre-wrap; word-wrap: break-word;">{{ $post->content }}</p>
        </div>
    @endif

    {{-- Location --}}
    @if ($post->hasLocation())
        <div class="px-3 pb-2 small">
            <i class="bi bi-geo-alt-fill text-danger"></i>
            @if ($post->mapsUrl())
                <a href="{{ $post->mapsUrl() }}" target="_blank" rel="noopener" class="text-decoration-none">
                    {{ $post->location ?? 'Lihat di peta' }}
                </a>
            @else
                {{ $post->location }}
            @endif
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

                        {{-- Input Lokasi untuk Komentar --}}
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <div class="input-group input-group-sm flex-grow-1" style="max-width:320px;">
                                <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="location"
                                    class="form-control"
                                    placeholder="Lokasi komentar (opsional)">
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-friendster" onclick="detectLocation(this)">
                                <i class="bi bi-crosshair"></i> Deteksi
                            </button>
                        </div>

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
                        @error('location')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>