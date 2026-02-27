<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Forgot password?</h2>
    <p class="text-muted small mb-4">Enter your email and we'll send you a reset link.</p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus class="form-control" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="d-flex flex-wrap gap-2">
            <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">Back to login</a>
        </div>
    </form>
</x-guest-layout>
