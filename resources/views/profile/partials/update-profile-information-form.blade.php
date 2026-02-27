<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="mb-3">
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" class="form-control" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="mb-3">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" class="form-control" />
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="small text-muted mt-2">
                {{ __('Your email address is unverified.') }}
                <button type="submit" form="send-verification" class="btn btn-link p-0 align-baseline text-primary">{{ __('Click here to re-send the verification email.') }}</button>
            </p>
            @if (session('status') === 'verification-link-sent')
                <p class="small text-success mt-2">{{ __('A new verification link has been sent to your email address.') }}</p>
            @endif
        @endif
    </div>

    <div class="d-flex align-items-center gap-3">
        <x-primary-button>{{ __('Save') }}</x-primary-button>
        @if (session('status') === 'profile-updated')
            <span class="small text-muted">{{ __('Saved.') }}</span>
        @endif
    </div>
</form>
