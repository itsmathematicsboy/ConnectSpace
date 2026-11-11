@extends('layouts.app')
@section('title', 'Ubah Password - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        <div class="d-flex gap-2 mb-3">
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm">Profil</a>
            <a href="{{ route('profile.password.edit') }}" class="btn btn-friendster btn-sm">Ubah Password</a>
        </div>

        <div class="card-friendster p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-key me-2"></i> Ubah Password</h5>
            <p class="text-muted-2 small mb-4">Untuk keamanan akun, password lama harus diverifikasi terlebih dahulu. Password baru minimal 6 karakter.</p>

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="current_password" class="form-label fw-medium">Password Lama</label>
                    <input type="password" name="current_password" id="current_password"
                           class="form-control form-control-lg {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                           autocomplete="current-password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-medium">Password Baru</label>
                    <input type="password" name="password" id="password"
                           class="form-control form-control-lg {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           autocomplete="new-password" placeholder="Minimal 6 karakter">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-medium">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control form-control-lg {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                           autocomplete="new-password">
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-friendster"><i class="bi bi-shield-lock me-1"></i> Perbarui Password</button>
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection