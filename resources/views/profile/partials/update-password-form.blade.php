<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="mb-3">
        <x-input-label for="update_password_current_password" :value="__('Current Password')" />
        <x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="form-control" />
        <x-input-error :messages="$errors->updatePassword->get('current_password')" />
    </div>

    <div class="mb-3">
        <x-input-label for="update_password_password" :value="__('New Password')" />
        <x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" class="form-control" />
        <x-input-error :messages="$errors->updatePassword->get('password')" />
    </div>

    <div class="mb-4">
        <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-control" />
        <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
    </div>

    <div class="d-flex align-items-center gap-3">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (session('status') === 'password-updated')
            <span class="small text-muted">{{ __('Saved.') }}</span>
        @endif
    </div>
</form>
