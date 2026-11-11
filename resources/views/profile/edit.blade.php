@extends('layouts.app')
@section('title', 'Profil Saya - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Profile header card --}}
        <div class="card-friendster p-4 mb-4 text-center">
            <img src="{{ $user->avatarUrl() }}" alt="avatar" class="avatar avatar-lg mb-3" width="80" height="80">
            <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
            <div class="text-muted-2 small mb-2"><i class="bi bi-envelope me-1"></i>{{ $user->email }}</div>
            @if ($user->bio)
                <p class="text-muted-2 mb-0 px-3" style="max-width:540px;margin:0 auto;">{{ $user->bio }}</p>
            @else
                <p class="text-muted-2 mb-0 small fst-italic">Belum ada bio. Tambahkan di bawah.</p>
            @endif
        </div>

        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('profile.edit') }}" class="btn btn-friendster btn-sm">Profil</a>
            <a href="{{ route('profile.password.edit') }}" class="btn btn-outline-secondary btn-sm">Ubah Password</a>
        </div>

        {{-- Edit form --}}
        <div class="card-friendster p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-pencil-square me-2"></i> Edit Profil</h5>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label fw-medium">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name', $user->name) }}" autocomplete="name">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-medium text-muted-2">Email (tidak dapat diubah)</label>
                    <input type="email" id="email" class="form-control form-control-lg bg-light" value="{{ $user->email }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="bio" class="form-label fw-medium">Bio</label>
                    <textarea name="bio" id="bio" rows="3" class="form-control {{ $errors->has('bio') ? 'is-invalid' : '' }}"
                              placeholder="Ceritakan sedikit tentang dirimu...">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Maksimal 500 karakter.</div>
                </div>

                <div class="mb-4">
                    <label for="avatar" class="form-label fw-medium">Foto Profil</label>
                    <input type="file" name="avatar" id="avatar" class="form-control {{ $errors->has('avatar') ? 'is-invalid' : '' }}" accept="image/*">
                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Format: JPG, JPEG, PNG, WEBP, GIF. Maksimal 2MB.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-friendster"><i class="bi bi-save me-1"></i> Simpan Perubahan</button>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection