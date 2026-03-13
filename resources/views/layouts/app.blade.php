<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <script>
        (function () {
            var theme = localStorage.getItem('sharecart-theme') || 'light';
            if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ShareCart') }} @isset($title) – {{ $title }} @endisset</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/sharecart.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body class="d-flex flex-column min-vh-100">
    @auth
    <nav class="navbar navbar-expand-lg navbar-dark sharecart-nav">
        <div class="container">
            <a class="navbar-brand" href="{{ route('lists.index') }}">ShareCart</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('lists.*') ? 'active' : '' }}" href="{{ route('lists.index') }}">My lists</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}" href="{{ route('templates.index') }}">General Templates</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item">
                        <button type="button" class="nav-link theme-toggle border-0 bg-transparent text-inherit py-1 px-2" aria-label="Toggle dark mode" title="Toggle dark mode" style="font-size:1.2em;line-height:1;">
                            <span class="theme-icon-light">🌙</span>
                            <span class="theme-icon-dark d-none">☀️</span>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Log out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endauth
    @guest
    <nav class="navbar navbar-expand-lg navbar-dark sharecart-nav">
        <div class="container">
            <a class="navbar-brand" href="{{ route('join') }}">ShareCart</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link {{ request()->routeIs('join') ? 'active' : '' }}" href="{{ route('join') }}">Join by code</a>
                <a class="nav-link" href="{{ route('login') }}">Log in</a>
                <a class="nav-link" href="{{ route('register') }}">Register</a>
            </div>
        </div>
    </nav>
    @endguest

    <main class="flex-grow-1 py-4">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                    <span class="me-2">✓</span>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <span class="me-2">!</span>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @isset($header)
                <div class="mb-4">
                    {{ $header }}
                </div>
            @endisset
            {{ $slot }}
        </div>
    </main>

    <footer class="py-3 mt-auto sharecart-footer">
        <div class="container small text-center">ShareCart – shared grocery lists</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                var html = document.documentElement;
                var isDark = html.getAttribute('data-theme') === 'dark';
                var next = isDark ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                localStorage.setItem('sharecart-theme', next);
                document.querySelectorAll('.theme-icon-light').forEach(function (el) { el.classList.toggle('d-none', next === 'dark'); });
                document.querySelectorAll('.theme-icon-dark').forEach(function (el) { el.classList.toggle('d-none', next === 'light'); });
            });
            var theme = document.documentElement.getAttribute('data-theme');
            document.querySelectorAll('.theme-icon-light').forEach(function (el) { el.classList.toggle('d-none', theme === 'dark'); });
            document.querySelectorAll('.theme-icon-dark').forEach(function (el) { el.classList.toggle('d-none', theme === 'light'); });
        }
        document.body.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form.classList.contains('swal-confirm')) return;
            e.preventDefault();
            var title = form.getAttribute('data-swal-title') || 'Are you sure?';
            var text = form.getAttribute('data-swal-text') || '';
            var icon = form.getAttribute('data-swal-icon') || 'warning';
            var confirmText = form.getAttribute('data-swal-confirm-text') || 'Yes, remove it';
            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
    </script>
    @stack('scripts')
</body>
</html>
