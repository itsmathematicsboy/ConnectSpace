@extends('layouts.app')
@section('title', 'Daftar - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white rounded-4 mb-3" style="width:72px;height:72px;box-shadow:0 4px 12px rgba(101,88,245,.18);">
                <i class="bi bi-people-fill" style="color:var(--fs-primary);font-size:2rem;"></i>
            </div>
            <h2 class="fw-bold mb-1">Buat Akun Baru</h2>
            <p class="text-muted-2 mb-0">Bergabung dengan ruang sosial internal Friendster.</p>
        </div>

        <div class="card-friendster p-4 p-md-4">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label fw-medium">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name') }}" placeholder="Nama lengkap kamu" autocomplete="name" autofocus>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">Email</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}" placeholder="nama@perusahaan.com" autocomplete="email">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Minimal 6 karakter" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-medium">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                           placeholder="Ulangi password" autocomplete="new-password">
                    @error('password_confirmation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-friendster w-100 btn-lg">
                    <i class="bi bi-person-plus me-1"></i> Daftar & Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-muted-2 small mt-4 mb-0">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-decoration-none fw-medium" style="color:var(--fs-primary);">Masuk di sini</a>
        </p>
    </div>
</div>
@endsection