@php
    $isCommentOwner = $comment->user_id === Auth::id();
    $post = $post ?? $comment->post; // fallback kalau $post tidak di-pass dari parent
@endphp

<div class="comment-item d-flex align-items-start gap-2">
    <a href="{{ route('profile.show', $comment->user) }}" class="text-decoration-none">
        <img src="{{ $comment->user->avatarUrl() }}" alt="{{ $comment->user->name }}"
             class="avatar avatar-sm" width="36" height="36">
    </a>
    <div class="flex-grow-1">
        <div class="bg-light rounded-3 px-3 py-2 d-inline-block" style="max-width:100%;">
            <a href="{{ route('profile.show', $comment->user) }}" class="text-decoration-none">
                <div class="fw-semibold small text-dark">{{ $comment->user->name }}</div>
            </a>
            @if ($comment->comment)
                <div class="small" style="white-space: pre-wrap; word-wrap: break-word;">{{ $comment->comment }}</div>
            @endif
        </div>
        @if ($comment->image)
            <div class="mt-2">
                <img src="{{ $comment->imageUrl() }}" alt="gambar-komentar"
                     class="feed-comment-image border" loading="lazy">
            </div>
        @endif
        <div class="d-inline-flex align-items-center gap-2 mt-1" style="font-size:.75rem;">
            <span class="text-muted-2">
                {{ $comment->created_at->diffForHumans() }}
                &middot; {{ $comment->created_at->format('d M Y, H:i') }}
            </span>

            {{-- Tombol Balas --}}
            <button type="button"
                    class="btn btn-sm btn-link p-0 m-0 text-muted-2"
                    style="font-size:.75rem;"
                    data-bs-toggle="collapse"
                    data-bs-target="#reply-form-{{ $comment->id }}">
                <i class="bi bi-reply"></i> Balas
            </button>

            @if ($isCommentOwner)
                <button type="button" class="btn btn-sm btn-link p-0 m-0 text-muted-2"
                        style="font-size:.75rem;"
                        data-bs-toggle="modal"
                        data-bs-target="#editCommentModal-{{ $comment->id }}">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <form method="POST"
                      action="{{ route('posts.comments.destroy', ['post' => $comment->post_id, 'comment' => $comment->id]) }}"
                      onsubmit="return confirm('Hapus komentar ini? Balasan juga akan ikut terhapus.');"
                      style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-link p-0 m-0 text-danger"
                            style="font-size:.75rem;" title="Hapus" aria-label="Hapus">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </form>
            @endif
        </div>

        {{-- Form Reply (Collapse) --}}
        <div class="collapse mt-2" id="reply-form-{{ $comment->id }}">
            <form method="POST"
                  action="{{ route('posts.comments.store', $post) }}"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <div class="d-flex align-items-start gap-2">
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="me" class="avatar avatar-sm" width="28" height="28">
                    <div class="flex-grow-1">
                        <textarea name="comment" rows="1"
                                  class="form-control form-control-sm"
                                  placeholder="Balas ke {{ $comment->user->name }}..."></textarea>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div class="d-flex gap-1">
                                <label class="btn btn-light btn-sm mb-0" style="border:1px solid var(--fs-border); cursor:pointer;">
                                    <i class="bi bi-image"></i>
                                    <input type="file" name="image" accept="image/*" class="d-none">
                                </label>
                            </div>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-link text-muted-2"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#reply-form-{{ $comment->id }}">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-friendster btn-sm">
                                    <i class="bi bi-reply"></i> Kirim Balasan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- List Replies --}}
        @if ($comment->replies && $comment->replies->isNotEmpty())
            <div class="replies-list mt-2 ms-3 ps-3" style="border-left: 2px solid var(--fs-border);">
                @foreach ($comment->replies as $reply)
                    <div class="comment-item d-flex align-items-start gap-2 py-2">
                        <a href="{{ route('profile.show', $reply->user) }}" class="text-decoration-none">
                            <img src="{{ $reply->user->avatarUrl() }}" alt="{{ $reply->user->name }}"
                                class="avatar avatar-sm" width="28" height="28">
                        </a>
                        <div class="flex-grow-1">
                            <div class="bg-light rounded-3 px-3 py-1 d-inline-block" style="max-width:100%;">
                               <a href="{{ route('profile.show', $reply->user) }}" class="text-decoration-none">
                                    <div class="fw-semibold small text-dark">{{ $reply->user->name }}</div>
                                </a>
                                @if ($reply->comment)
                                    <div class="small" style="white-space: pre-wrap; word-wrap: break-word;">{{ $reply->comment }}</div>
                                @endif
                            </div>
                            @if ($reply->image)
                                <div class="mt-1">
                                    <img src="{{ $reply->imageUrl() }}" alt="gambar-balasan"
                                         class="feed-comment-image border" loading="lazy"
                                         style="max-height:160px;">
                                </div>
                            @endif
                            <div class="d-inline-flex align-items-center gap-2 mt-1" style="font-size:.7rem;">
                                <span class="text-muted-2">
                                    {{ $reply->created_at->diffForHumans() }}
                                </span>
                                @if ($reply->user_id === Auth::id())
                                    <form method="POST"
                                          action="{{ route('posts.comments.destroy', ['post' => $reply->post_id, 'comment' => $reply->id]) }}"
                                          onsubmit="return confirm('Hapus balasan ini?');"
                                          style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link p-0 m-0 text-danger"
                                                style="font-size:.7rem;">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Modal Edit Komentar (hanya untuk pemilik) --}}
@if ($isCommentOwner)
<div class="modal fade" id="editCommentModal-{{ $comment->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"
                  action="{{ route('posts.comments.update', ['post' => $comment->post_id, 'comment' => $comment->id]) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Edit Komentar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Komentar</label>
                        <textarea name="comment" rows="3" class="form-control"
                                  placeholder="Tulis komentar...">{{ old('comment', $comment->comment) }}</textarea>
                    </div>
                    @if ($comment->image)
                        <div class="mb-3">
                            <label class="form-label fw-medium small">Gambar saat ini:</label><br>
                            <img src="{{ $comment->imageUrl() }}" alt="gambar-saat-ini"
                                 style="max-height:160px;border-radius:.5rem;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                       name="remove_image" value="1"
                                       id="rm-cmt-{{ $comment->id }}">
                                <label class="form-check-label small text-danger"
                                       for="rm-cmt-{{ $comment->id }}">Hapus gambar ini</label>
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
                    <button type="submit" class="btn btn-friendster">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif