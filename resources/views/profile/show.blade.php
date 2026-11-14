@extends('layouts.app')
@section('title', $user->name . ' - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- Profile Header Card --}}
        <div class="card-friendster p-4 mb-4">
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
                     class="avatar avatar-lg" width="80" height="80">
                <div class="text-center text-md-start flex-grow-1">
                    <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                    <div class="text-muted-2 small mb-2">
                        <i class="bi bi-envelope me-1"></i>{{ $user->email }}
                    </div>
                    @if ($user->bio)
                        <p class="text-muted-2 mb-2">{{ $user->bio }}</p>
                    @else
                        <p class="text-muted-2 mb-2 small fst-italic">Belum ada bio.</p>
                    @endif
                    <div class="text-muted-2 small">
                        <i class="bi bi-calendar3 me-1"></i>
                        Bergabung sejak {{ $user->created_at->format('d M Y') }}
                    </div>
                </div>

                @if ($user->id === Auth::id())
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-friendster btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profil
                    </a>
                    <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-key me-1"></i> Ubah Password
                    </a>
                </div>
                @endif
            </div>

            {{-- Statistik --}}
            <hr class="my-3">
            <div class="row text-center g-2">
                <div class="col-4">
                    <div class="fw-bold fs-5" style="color:var(--fs-primary);">{{ $totalPosts }}</div>
                    <div class="text-muted-2 small">Postingan</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold fs-5" style="color:var(--fs-primary);">{{ $totalComments }}</div>
                    <div class="text-muted-2 small">Komentar</div>
                </div>
                <div class="col-4">
                    <div class="fw-bold fs-5" style="color:var(--fs-primary);">{{ $totalLikesReceived }}</div>
                    <div class="text-muted-2 small">Like Diterima</div>
                </div>
            </div>
        </div>

        {{-- List Posts --}}
        <h5 class="fw-semibold text-muted-2 mb-3">
            <i class="bi bi-collection me-1"></i>
            Postingan {{ $user->id === Auth::id() ? 'Saya' : $user->name }}
        </h5>

        @forelse ($posts as $post)
            @include('posts.partials.post', ['post' => $post])
        @empty
            <div class="card-friendster p-5 text-center">
                <div class="empty-state">
                    <i class="bi bi-inbox d-block mb-3"></i>
                    <h6 class="fw-bold">Belum ada postingan</h6>
                    <p class="small mb-0">
                        @if ($user->id === Auth::id())
                            Yuk mulai berbagi sesuatu di Friendster.
                        @else
                            User ini belum pernah memposting apapun.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse

        <div class="d-flex justify-content-center mt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection