<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Confirm password</h2>
    <p class="text-muted small mb-4">This is a secure area. Please confirm your password to continue.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" class="form-control" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <x-primary-button>{{ __('Confirm') }}</x-primary-button>
    </form>
</x-guest-layout>
