<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">General Templates</h2>
        </div>
    </x-slot>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        @forelse($templates as $template)
            <div class="col">
                <div class="card h-100 sharecart-card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $template->name }}</h5>
                        <p class="card-text text-muted small">
                            {{ $template->items_count }} items
                        </p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-info text-dark">System Template</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="display-1 text-muted mb-4">📋</div>
                <h4>No templates yet</h4>
                <p class="text-muted">System templates will appear here once seeded.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
