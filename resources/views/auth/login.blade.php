@extends('layouts.app')
@section('title', 'Masuk - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-4 mb-3" style="width:72px;height:72px;box-shadow:0 4px 12px rgba(101,88,245,.18);">
                <i class="bi bi-people-fill" style="color:var(--fs-primary);font-size:2rem;"></i>
            </div>
            <h2 class="fw-bold mb-1">Selamat Datang Kembali</h2>
            <p class="text-muted-2 mb-0">Masuk untuk melanjutkan ke Friendster.</p>
        </div>

        <div class="card-friendster p-4 p-md-4">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">Email</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="nama@perusahaan.com" autocomplete="email" autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Password kamu" autocomplete="current-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label text-muted-2" for="remember">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="btn btn-friendster w-100 btn-lg">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-muted-2 small mt-4 mb-0">
            Belum punya akun? <a href="{{ route('register') }}" class="text-decoration-none fw-medium" style="color:var(--fs-primary);">Daftar di sini</a>
        </p>
    </div>
</div>
@endsection