<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Reset password</h2>
    <p class="text-muted small mb-4">Choose a new password for your account.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" class="form-control" />
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
        <x-primary-button>{{ __('Reset Password') }}</x-primary-button>
    </form>
</x-guest-layout>
