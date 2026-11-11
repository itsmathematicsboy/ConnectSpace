@extends('layouts.app')
@section('title', 'Halaman Tidak Ditemukan - Friendster')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 text-center py-5">
        <div class="empty-state">
            <i class="bi bi-compass d-block mb-3"></i>
            <h2 class="fw-bold">404</h2>
            <p class="text-muted-2 mb-4">Halaman atau postingan yang kamu cari tidak ditemukan. Mungkin sudah dihapus atau tidak pernah ada.</p>
            <a href="{{ route('home') }}" class="btn btn-friendster">
                <i class="bi bi-house-door me-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection