@extends('layouts.app')
@section('title', '419 - Sesi Kedaluwarsa - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
        <div class="empty-state">
            <i class="bi bi-clock-history d-block mb-3"></i>
            <h2 class="fw-bold">419</h2>
            <p class="text-muted-2 mb-4">Sesi kamu telah kedaluwarsa karena tidak ada aktivitas dalam waktu lama. Silakan muat ulang halaman dan coba lagi.</p>
            <a href="{{ route('login') }}" class="btn btn-friendster">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Lagi
            </a>
        </div>
    </div>
</div>
@endsection