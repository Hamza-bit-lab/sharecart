<x-app-layout>
    <x-slot name="header">
        <h1 class="h2 mb-0">New list</h1>
    </x-slot>

    <div class="card sharecart-card">
        <div class="card-body">
            <form action="{{ route('lists.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">List name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Weekly groceries">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="due_date" class="form-label">Shopping / trip date <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date') }}" min="{{ date('Y-m-d') }}">
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Create list</button>
                    <a href="{{ route('lists.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
