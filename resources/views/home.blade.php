@extends('layouts.app')
@section('title', 'Beranda - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        {{-- === Create Post Form === --}}
        <div class="card-friendster p-3 p-md-4 mb-4">
            <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="d-flex align-items-start gap-3">
                    <img src="{{ Auth::user()->avatarUrl() }}" alt="avatar" class="avatar avatar-md" width="44" height="44">
                    <div class="flex-grow-1">
                        <textarea name="content" id="postContent" rows="3"
                            class="form-control form-control-lg border-0 shadow-none {{ $errors->has('content') ? 'is-invalid' : '' }}"
                            placeholder="Apa yang kamu pikirkan, {{ Auth::user()->name }}?"
                            oninput="updateCharCounter(this, 'postContentCounter')">{{ old('content') }}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-muted-2" style="font-size:.75rem;">Maks. 500 karakter</span>
                            <span class="text-muted-2" style="font-size:.75rem;"><span id="postContentCounter">0</span>/500</span>
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                {{-- Input Lokasi --}}
                <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                    <div class="input-group input-group-sm flex-grow-1" style="max-width:380px;">
                        <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" name="location" id="postLocation"
                            class="form-control"
                            value="{{ old('location') }}"
                            placeholder="Lokasi (opsional)">
                        <input type="hidden" name="latitude" id="postLatitude" value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="postLongitude" value="{{ old('longitude') }}">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-friendster" onclick="detectLocation(this)">
                        <i class="bi bi-crosshair"></i> Deteksi Lokasi Saya
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-muted-2 p-0" onclick="clearLocation(this)">
                        <i class="bi bi-x"></i> Hapus
                    </button>
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <label for="postImage" class="btn btn-light btn-sm mb-0 cursor-pointer" style="border:1px solid var(--fs-border);">
                        <i class="bi bi-image me-1" style="color:var(--fs-primary);"></i>
                        <span class="small fw-medium">Tambah Gambar</span>
                        <input type="file" id="postImage" name="image" accept="image/*" class="d-none" onchange="document.getElementById('postImageName').textContent = this.files[0] ? this.files[0].name : '';">
                    </label>
                    <span id="postImageName" class="text-muted-2 small flex-grow-1 ms-2 truncate-2"></span>
                    <button type="submit" class="btn btn-friendster btn-sm">
                        <i class="bi bi-send me-1"></i> Posting
                    </button>
                </div>
                @error('image')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                @error('location')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </form>
        </div>

        {{-- === Feed === --}}
        <h5 class="fw-semibold text-muted-2 mb-3"><i class="bi bi-newspaper me-1"></i> Feed Terbaru</h5>

        @forelse ($posts as $post)
            @include('posts.partials.post', ['post' => $post])
        @empty
            <div class="card-friendster p-5 text-center">
                <div class="empty-state">
                    <i class="bi bi-people d-block mb-3"></i>
                    <h6 class="fw-bold">Belum ada postingan</h6>
                    <p class="small mb-0">Jadilah yang pertama berbagi sesuatu di Friendster.</p>
                </div>
            </div>
        @endforelse

        <div class="d-flex justify-content-center mt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection