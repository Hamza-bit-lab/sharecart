<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Create account</h2>
    <p class="text-muted small mb-4">Start sharing grocery lists with others</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" class="form-control" />
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" class="form-control" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="mb-3">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" class="form-control" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div class="mb-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <a class="auth-link small" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-primary-button>{{ __('Register') }}</x-primary-button>
        </div>
    </form>
    <p class="auth-divider text-center mt-4 mb-0">
        Already have an account? <a href="{{ route('login') }}" class="auth-link">Log in</a>
    </p>
</x-guest-layout>
