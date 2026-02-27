<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ShareCart') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/sharecart.css') }}" rel="stylesheet">
</head>
<body class="welcome-page">
    <div class="welcome-page__bg">
        <div class="welcome-page__gradient"></div>
        <div class="welcome-page__pattern"></div>
    </div>
    <div class="welcome-page__content position-relative">
        <div class="welcome-page__hero text-center mb-4">
            <h1 class="welcome-page__title">ShareCart</h1>
            <p class="welcome-page__tagline mb-0">Shared grocery lists that stay in sync with everyone.</p>
        </div>

        @auth
            <div class="d-flex gap-3 flex-wrap justify-content-center mt-4">
                <a href="{{ route('lists.index') }}" class="btn btn-light btn-lg px-4 rounded-pill fw-semibold">Go to my lists</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-lg px-4 rounded-pill">Log out</button>
                </form>
            </div>
        @else
            <div class="welcome-page__panel mx-auto mt-4">
                <p class="welcome-page__panel-subtitle text-center mb-4">
                    <span class="fw-semibold">Two easy ways to get into a list:</span>
                    <span class="d-block small mt-1">Enter a list code if someone shared one with you, or log in to see and manage your own lists.</span>
                </p>
                <div class="row g-4 align-items-stretch justify-content-center">
                    <div class="col-12 col-md-6">
                        <div class="welcome-page__option h-100">
                            <h2 class="welcome-page__option-title">Access with a code</h2>
                            <p class="welcome-page__option-text">No account needed. Enter the 5‑digit code from the list owner to open and edit that list.</p>
                            <form action="{{ route('join.submit') }}" method="POST" class="mt-3">
                                @csrf
                                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
                                    <input
                                        type="text"
                                        name="code"
                                        class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                                        placeholder="5‑digit code"
                                        value="{{ old('code') }}"
                                        maxlength="5"
                                        pattern="[0-9A-Za-z]{5}"
                                        autocomplete="off"
                                        style="max-width: 10rem; margin-inline: auto;"
                                    >
                                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-semibold">
                                        Access list
                                    </button>
                                </div>
                                @error('code')
                                    <div class="invalid-feedback d-block text-center mt-2">{{ $message }}</div>
                                @enderror
                                @if (session('error'))
                                    <p class="small text-danger mt-2 mb-0 text-center">{{ session('error') }}</p>
                                @endif
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="welcome-page__option h-100">
                            <h2 class="welcome-page__option-title">Or log in / sign up</h2>
                            <p class="welcome-page__option-text">Create your own lists, share them with others, and keep everything organised in one place.</p>
                            <div class="d-flex flex-column gap-2 mt-3">
                                <a href="{{ route('login') }}" class="btn btn-light btn-lg fw-semibold text-primary-emphasis">
                                    Log in to my lists
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg fw-semibold">
                                        Create a free account
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
