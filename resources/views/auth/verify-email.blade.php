<x-guest-layout>
    <h2 class="h5 fw-bold mb-1">Verify your email</h2>
    <p class="text-muted small mb-4">Thanks for signing up! Click the link we sent to your email to verify your account. Didn't receive it? We can send another.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small py-2 mb-4">
            {{ __('A new verification link has been sent to the email address you provided.') }}
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link p-0 text-muted text-decoration-none">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>
