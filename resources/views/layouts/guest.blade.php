<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ShareCart') }} @isset($title) – {{ $title }} @endisset</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/sharecart.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-page__bg">
        <div class="auth-page__gradient"></div>
        <div class="auth-page__pattern"></div>
    </div>
    <div class="auth-page__content position-relative">
        <a href="{{ url('/') }}" class="auth-page__logo">ShareCart</a>
        <div class="auth-card">
            <div class="auth-card__body">
                {{ $slot }}
            </div>
        </div>
        <p class="auth-page__footer text-center mt-4 small text-white-50">Shared grocery lists, together.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
