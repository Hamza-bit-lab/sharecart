@if ($items->isEmpty())
    <div class="p-4 text-muted text-center">No items yet. Add one above.</div>
@else
    <ul class="list-group list-group-flush">
        @foreach ($items as $item)
            <li class="list-group-item d-flex align-items-center gap-2" data-item-id="{{ $item->id }}">
                <form action="{{ route('lists.items.update', [$item->groceryList, $item]) }}" method="POST" class="d-flex align-items-center gap-2 flex-grow-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="completed" value="{{ $item->completed ? '0' : '1' }}">
                    <input type="checkbox" class="form-check-input item-toggle" {{ $item->completed ? 'checked' : '' }} title="Toggle completed" onchange="this.form.submit()">
                    <span class="{{ $item->completed ? 'text-decoration-line-through text-muted' : '' }}">
                        {{ $item->name }}{{ $item->quantity > 1 ? ' × ' . $item->quantity : '' }}
                    </span>
                </form>
                <form action="{{ route('lists.items.destroy', [$item->groceryList, $item]) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Remove this item?" data-swal-text="This item will be removed from the list.">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                </form>
            </li>
        @endforeach
    </ul>
@endif
