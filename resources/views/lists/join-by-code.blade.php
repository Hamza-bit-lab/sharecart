<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h1 class="h2 mb-0">Join a list by code</h1>
            @auth
                <a href="{{ route('lists.index') }}" class="btn btn-outline-secondary btn-sm">← My lists</a>
            @else
                <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">← Home</a>
            @endauth
        </div>
    </x-slot>

    <p class="text-muted mb-4">Enter the 5-digit code shared by the list owner. You can view and edit the list without creating an account.</p>

    <div class="card sharecart-card mb-4" style="max-width: 24rem;">
        <div class="card-body">
            <form action="{{ route('join.submit') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-12">
                    <label for="join-code" class="form-label small fw-semibold">List code</label>
                    <input type="text" name="code" id="join-code" class="form-control form-control-lg text-center @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. 12345" maxlength="5" pattern="[0-9A-Za-z]{5}" autocomplete="off" autofocus>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100">Join list</button>
                </div>
            </form>
        </div>
    </div>

    @auth
        <p class="small text-muted">You’re logged in — you’ll be added as a collaborator and can access the list from My lists.</p>
    @else
        <p class="small text-muted">You’re not logged in — you’ll get temporary access to the list in this browser. Create an account to keep access across devices.</p>
    @endauth
</x-app-layout>
