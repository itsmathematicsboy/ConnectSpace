<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Friendster')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --fs-primary: #6558f5;
            --fs-primary-dark: #4f44d8;
            --fs-bg: #f4f5f9;
            --fs-border: #e7e9f3;
            --fs-text-muted: #6b7280;
        }
        body {
            background-color: var(--fs-bg);
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #1f2937;
        }
        .navbar-friendster {
            background: #ffffff;
            border-bottom: 1px solid var(--fs-border);
            box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }
        .navbar-friendster .navbar-brand {
            color: var(--fs-primary);
            font-weight: 800;
            letter-spacing: -.02em;
        }
        .navbar-friendster .navbar-brand:hover { color: var(--fs-primary-dark); }
        .btn-friendster {
            background-color: var(--fs-primary);
            border-color: var(--fs-primary);
            color: #fff;
            font-weight: 600;
        }
        .btn-friendster:hover { background-color: var(--fs-primary-dark); border-color: var(--fs-primary-dark); color:#fff; }
        .btn-outline-friendster {
            color: var(--fs-primary);
            border-color: var(--fs-primary);
            font-weight: 600;
        }
        .btn-outline-friendster:hover {
            background-color: var(--fs-primary);
            color: #fff;
        }
        .card-friendster {
            background: #fff;
            border: 1px solid var(--fs-border);
            border-radius: .85rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }
        .avatar {
            border-radius: 50%;
            object-fit: cover;
            background: #eef0f6;
            flex-shrink: 0;
        }
        .avatar-sm { width: 36px; height: 36px; }
        .avatar-md { width: 44px; height: 44px; }
        .avatar-lg { width: 80px; height: 80px; }
        .feed-post .post-image {
            max-height: 540px;
            width: 100%;
            object-fit: cover;
            background: #f3f4f6;
        }
        .feed-comment-image {
            max-height: 240px;
            border-radius: .5rem;
        }
        .like-btn.liked { color: #ef4444; }
        .like-btn { color: #6b7280; font-weight: 600; transition: color .15s; }
        .like-btn:hover { color: #ef4444; }
        .text-muted-2 { color: var(--fs-text-muted); }
        .form-control:focus, .form-select:focus {
            border-color: var(--fs-primary);
            box-shadow: 0 0 0 .2rem rgba(101, 88, 245, .15);
        }
        .truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .comment-list .comment-item { padding: .65rem 0; border-top: 1px dashed var(--fs-border); }
        .comment-list .comment-item:first-child { border-top: none; }
        .empty-state {
            text-align: center; padding: 3rem 1rem; color: var(--fs-text-muted);
        }
        .empty-state .bi { font-size: 3rem; opacity: .4; }
        .nav-link.active { color: var(--fs-primary) !important; font-weight: 600; }
        .toast-container { z-index: 1100; }
    </style>
    @stack('styles')
</head>
<body>

@auth
<nav class="navbar navbar-expand-lg navbar-friendster sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            <i class="bi bi-people-fill me-2 fs-4"></i> Friendster
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link @if(Route::is('home*')) active @endif" href="{{ route('home') }}">
                        <i class="bi bi-house-door me-1"></i> Beranda
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="{{ Auth::user()->avatarUrl() }}" alt="avatar" class="avatar avatar-sm me-2" width="36" height="36">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                        <li><a class="dropdown-item" href="{{ route('profile.password.edit') }}"><i class="bi bi-key me-2"></i> Ubah Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
@endauth

<main class="container py-4">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-circle me-1"></i> Terjadi kesalahan:</strong>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @yield('content')
</main>

<footer class="container text-center text-muted-2 small py-4 mt-5">
    Friendster &middot; Internal Social Media MVP &middot; Laravel {{ \Illuminate\Foundation\Application::VERSION }}
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>