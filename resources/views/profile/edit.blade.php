<x-app-layout>
    <x-slot name="header">
        <h1 class="h2 mb-0">Profile</h1>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="profile-section">
                <h2>{{ __('Profile Information') }}</h2>
                <p class="text-muted">{{ __("Update your account's profile information and email address.") }}</p>
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="profile-section">
                <h2>{{ __('Update Password') }}</h2>
                <p class="text-muted">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
                @include('profile.partials.update-password-form')
            </div>

            <div class="profile-section">
                <h2>{{ __('Delete Account') }}</h2>
                <p class="text-muted">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}</p>
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
