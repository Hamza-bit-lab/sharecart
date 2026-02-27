<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Welcome back</h2>
    <p class="text-muted small mb-4">Sign in to your account</p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" class="form-control" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" class="form-control" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="form-check mb-4">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label small text-muted">{{ __('Remember me') }}</label>
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            @if (Route::has('password.request'))
                <a class="auth-link small" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
            <x-primary-button>{{ __('Log in') }}</x-primary-button>
        </div>
    </form>
    <p class="auth-divider text-center mt-4 mb-0">
        Don't have an account? <a href="{{ route('register') }}" class="auth-link">Register</a>
    </p>
</x-guest-layout>
