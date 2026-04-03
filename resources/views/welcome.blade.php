<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ShareCart') }} - Collaborative Shopping</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link href="{{ asset('css/sharecart.css') }}" rel="stylesheet">
    
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
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <nav class="navbar navbar-expand-lg landing-header transition-colors">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-cart3"></i> ShareCart
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navcol">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navcol">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How it works</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <!-- Theme Toggle -->
                    <button type="button" class="theme-toggle border-0 bg-transparent text-inherit p-0" aria-label="Toggle dark mode" style="font-size:1.25rem;">
                        <span class="theme-icon-light text-dark">🌙</span>
                        <span class="theme-icon-dark text-white d-none">☀️</span>
                    </button>
                    
                    @auth
                        <a href="{{ route('lists.index') }}" class="btn btn-primary rounded-pill px-4 fw-semibold">Go to My Lists</a>
                    @else
                        <a href="{{ route('login') }}" class="nav-link fw-semibold">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn-primary rounded-pill px-4 fw-semibold">Sign up free</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="landing-main flex-grow-1">
        
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-mesh"></div>
            <div class="container position-relative">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 hero-content">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white shadow-sm mb-4 border" style="font-size: 0.875rem; font-weight: 600; color: #1a9277;">
                            <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                            Real-time syncing enabled
                        </div>
                        <h1 class="hero-title">
                            Grocery shopping,<br>
                            <span>perfectly synced.</span>
                        </h1>
                        <p class="hero-subtitle">
                            Create collaborative grocery lists, share them with your family or roommates, and check off items together in real-time. Never forget the milk again.
                        </p>
                        
                        @guest
                        <div class="join-card mt-5">
                            <h5 class="fw-bold mb-3 text-dark">Got a invite code?</h5>
                            <form action="{{ route('join.submit') }}" method="POST">
                                @csrf
                                <div class="d-flex flex-column flex-sm-row gap-3">
                                    <input type="text" name="code" class="form-control form-control-lg text-center fw-bold @error('code') is-invalid @enderror" placeholder="5-DIGIT CODE" maxlength="5" autocomplete="off" style="max-width: 200px;">
                                    <button type="submit" class="btn btn-primary btn-lg px-4 fw-bold">Join List</button>
                                </div>
                                @error('code')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                                @if (session('error'))
                                    <p class="small text-danger mt-2 mb-0">{{ session('error') }}</p>
                                @endif
                            </form>
                        </div>
                        @endguest
                    </div>
                    <div class="col-lg-6 text-center text-lg-end">
                        <div class="hero-image-wrapper">
                            <img src="{{ asset('images/hero_img.png') }}" alt="Collaborative cart illustration" class="hero-image">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="features-section">
            <div class="container">
                <div class="text-center mb-5 pb-3">
                    <span class="feature-badge">Everything you need</span>
                    <h2 class="fw-bold fs-1 mb-3">Smarter way to shop together</h2>
                    <p class="text-muted fs-5 max-w-2xl mx-auto" style="max-width: 600px;">ShareCart brings order to your grocery runs with powerful features designed for households and groups.</p>
                </div>
                
                <div class="row g-4">
                    <!-- Feature 1 -->
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon-wrapper">
                                <i class="bi bi-phone-vibrate"></i>
                            </div>
                            <h3 class="h4 fw-bold mb-3">Instant Nudges</h3>
                            <p class="text-muted mb-0">At the store? Tap the "Nudge!" button to instantly send a push notification to all list members asking for last-minute additions.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 2 -->
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                                <i class="bi bi-cloud-sync"></i>
                            </div>
                            <h3 class="h4 fw-bold mb-3">Real-time Sync</h3>
                            <p class="text-muted mb-0">Watch items get checked off instantly as your partner shops. No more duplicate purchases or refreshing the page constantly.</p>
                        </div>
                    </div>
                    
                    <!-- Feature 3 -->
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon-wrapper" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                                <i class="bi bi-calculator"></i>
                            </div>
                            <h3 class="h4 fw-bold mb-3">Fair Split</h3>
                            <p class="text-muted mb-0">Track who paid for what. ShareCart automatically calculates the "Fair Share" and shows exactly who owes whom after the trip.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row align-items-center mt-5 pt-5">
                    <div class="col-lg-6 order-2 order-lg-1">
                        <img src="{{ asset('images/sync_img.png') }}" alt="Devices syncing" class="feature-image">
                    </div>
                    <div class="col-lg-5 offset-lg-1 order-1 order-lg-2 mb-4 mb-lg-0">
                        <span class="feature-badge bg-primary text-white mb-3">Cross-Platform Sync</span>
                        <h3 class="fw-bold mb-4" style="font-size: 2.5rem; letter-spacing: -0.02em;">Works seamlessly on all your devices.</h3>
                        <p class="text-muted fs-5 mb-4">Whether you're planning on your laptop at home or checking off items rapidly on your phone in the aisle, ShareCart keeps everything strictly in sync.</p>
                        <ul class="list-unstyled d-flex flex-column gap-3">
                            <li class="d-flex align-items-center gap-3">
                                <i class="bi bi-check-circle-fill text-primary mt-1"></i>
                                <span class="fw-medium text-dark">Claim specific items to buy</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <i class="bi bi-check-circle-fill text-primary mt-1"></i>
                                <span class="fw-medium text-dark">Join lists instantly via 5-digit code or link</span>
                            </li>
                            <li class="d-flex align-items-center gap-3">
                                <i class="bi bi-check-circle-fill text-primary mt-1"></i>
                                <span class="fw-medium text-dark">Create reusable grocery templates</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <h2 class="cta-title">Ready to simplify your shopping?</h2>
                        <p class="fs-5 text-white-50 mb-5">Join thousands of households already saving time and avoiding duplicate purchases with ShareCart.</p>
                        @guest
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary shadow-lg" style="font-size: 1.1rem;">
                                Create Your Free Account
                            </a>
                        @else
                            <a href="{{ route('lists.index') }}" class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-primary shadow-lg" style="font-size: 1.1rem;">
                                Go to My Dashboard
                            </a>
                        @endguest
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row gy-4 border-bottom pb-4 mb-4" style="border-color: var(--sc-border) !important;">
                <div class="col-lg-4">
                    <a href="{{ url('/') }}" class="footer-logo d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-cart3"></i> ShareCart
                    </a>
                    <p class="text-muted small pe-lg-4">
                        The ultimate collaborative grocery list app for families, roommates, and teams. Stay synced, stay organized.
                    </p>
                </div>
                <div class="col-6 col-md-3 offset-lg-1">
                    <h5 class="fw-bold mb-3 fs-6">Product</h5>
                    <ul class="list-unstyled footer-links d-flex flex-column gap-2">
                        <li><a href="#features">Features</a></li>
                        <li><a href="{{ route('login') }}">Log In</a></li>
                        <li><a href="{{ route('register') }}">Sign Up</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3">
                    <h5 class="fw-bold mb-3 fs-6">Legal</h5>
                    <ul class="list-unstyled footer-links d-flex flex-column gap-2">
                        <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}">Terms & Conditions</a></li>
                        <li><a href="{{ route('faq') }}">FAQs</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
                <span>&copy; {{ date('Y') }} ShareCart. All rights reserved.</span>
                <span class="mt-2 mt-md-0">Built with <i class="bi bi-heart-fill text-danger mx-1"></i> to make shopping better.</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Toggle Logic -->
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
        });
    </script>
</body>
</html>
