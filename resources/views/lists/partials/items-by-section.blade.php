@php
    $sectionLabels = \App\Services\SuggestionCategories::sectionLabels();
    $totalItems = 0;
    foreach ($groupedItems as $items) {
        $totalItems += count($items);
    }
@endphp
@if ($totalItems === 0)
    <div class="p-4 text-muted text-center">No items yet. Add one above.</div>
@else
    @foreach ($groupedItems as $sectionKey => $items)
        @if (count($items) > 0)
            @php
                $sectionLabel = $sectionLabels[$sectionKey] ?? ucfirst($sectionKey);
                $sectionId = 'section-' . $sectionKey;
            @endphp
            <div class="border-bottom" data-section="{{ $sectionKey }}">
                <div class="section-header px-3 py-3 bg-light d-flex align-items-center justify-content-between border-bottom" data-bs-toggle="collapse" data-bs-target="#{{ $sectionId }}" aria-expanded="true" aria-controls="{{ $sectionId }}" role="button" tabindex="0" style="cursor: pointer;">
                    <span class="d-flex align-items-center gap-2">
                        <span class="section-toggle-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </span>
                        <span class="fw-semibold text-dark">{{ $sectionLabel }}</span>
                    </span>
                    <span class="badge bg-secondary rounded-pill">{{ count($items) }}</span>
                </div>
                <div class="collapse show" id="{{ $sectionId }}">
                    <div class="row g-2 p-2 list-items-grid">
                        @foreach ($items as $item)
                            <div class="col-6 col-md-6" data-item-id="{{ $item->id }}">
                                <div class="list-item-card border rounded p-2 h-100 d-flex align-items-center justify-content-between gap-2">
                                    <form action="{{ route('lists.items.update', [$item->groceryList, $item]) }}" method="POST" class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="completed" value="{{ $item->completed ? '0' : '1' }}">
                                        <input type="checkbox" class="form-check-input item-toggle flex-shrink-0" {{ $item->completed ? 'checked' : '' }} title="Toggle purchased" onchange="this.form.submit()">
                                        <span class="{{ $item->completed ? 'text-decoration-line-through text-muted' : '' }} text-truncate small">
                                            {{ $item->name }}{{ $item->quantity > 1 ? ' × ' . $item->quantity : '' }}
                                            
                                            @if ($item->completed)
                                                @php
                                                    $completedByName = $item->completedBy->name ?? $item->completed_by_name;
                                                @endphp
                                                @if ($completedByName)
                                                    <span class="d-block very-small text-muted">(purchased by {{ $completedByName }})</span>
                                                @endif
                                            @elseif ($item->claimed_by_user_id || $item->claimed_by_name)
                                                @php
                                                    $claimedByName = $item->claimedByUser->name ?? $item->claimed_by_name;
                                                @endphp
                                                <span class="d-block very-small text-primary fw-semibold">
                                                    <i class="bi bi-person-check me-1"></i>{{ $claimedByName }} will buy this
                                                </span>
                                            @endif
                                        </span>
                                    </form>
                                    
                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                        @if (!$item->completed)
                                            @if ($item->claimed_by_user_id || $item->claimed_by_name)
                                                {{-- Unclaim Button --}}
                                                <form action="{{ route('lists.items.unclaim', [$item->groceryList, $item]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-link text-muted p-0 very-small text-decoration-none" title="Unclaim">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @else
                                                {{-- Claim Button --}}
                                                <form action="{{ route('lists.items.claim', [$item->groceryList, $item]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill very-small fw-bold">
                                                        Claim
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <form action="{{ route('lists.items.destroy', [$item->groceryList, $item]) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Remove this item?" data-swal-text="This item will be removed from the list.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1 border-0" title="Remove">×</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endif
