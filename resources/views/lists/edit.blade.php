<x-app-layout>
    <x-slot name="header">
        <h1 class="h2 mb-0">Edit list</h1>
    </x-slot>

    <div class="card sharecart-card">
        <div class="card-body">
            <form action="{{ route('lists.update', $list) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label for="name" class="form-label">List name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $list->name) }}" required maxlength="255">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label d-block">List Icon / Emoji <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <input type="hidden" name="icon" id="icon-input" value="{{ old('icon', $list->getRawOriginal('icon')) }}">
                        @foreach(App\Models\GroceryList::RECOMMENDED_ICONS as $icon)
                            <button type="button" class="btn btn-outline-light border emoji-btn p-2 fs-4 {{ old('icon', $list->icon) === $icon ? 'active border-primary bg-light text-primary' : 'text-dark' }}" data-emoji="{{ $icon }}" style="width: 50px; height: 50px;">
                                <i class="bi {{ $icon }}"></i>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="mb-3">
                    <label for="due_date" class="form-label">Shopping / trip date <span class="text-muted fw-normal">(optional)</span></label>
                    <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $list->due_date?->format('Y-m-d')) }}">
                    @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Leave empty for no due date.</div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <a href="{{ route('lists.show', $list) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnContainer = document.querySelector('.d-flex.flex-wrap.gap-2.mb-2');
            const iconInput = document.getElementById('icon-input');
            
            btnContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.emoji-btn');
                if (!btn) return;
                
                // Reset all buttons
                document.querySelectorAll('.emoji-btn').forEach(b => {
                    b.classList.remove('active', 'border-primary', 'bg-light', 'text-primary');
                    b.classList.add('text-dark');
                });
                
                // Set active button
                btn.classList.add('active', 'border-primary', 'bg-light', 'text-primary');
                btn.classList.remove('text-dark');
                iconInput.value = btn.dataset.emoji;
            });
        });
    </script>
    @endpush
</x-app-layout>
