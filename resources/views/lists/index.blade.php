<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h1 class="h2 mb-0">My lists</h1>
            <a href="{{ route('lists.create') }}" class="btn btn-primary">+ New list</a>
        </div>
    </x-slot>

    <p class="text-muted mb-4">Create and manage your grocery lists. Share a list to collaborate in real time.</p>

    <div class="card sharecart-card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-2">
                <div class="col-12 col-md-auto">
                    <label for="index-join-code" class="form-label small fw-semibold mb-0">Join a list by code</label>
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <input type="text" name="code" id="index-join-code" class="form-control form-control-sm text-center" placeholder="5-digit code" maxlength="5" pattern="[0-9A-Za-z]{5}" form="index-join-form">
                </div>
                <div class="col-12 col-md-auto">
                    <form id="index-join-form" action="{{ route('join.submit') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm">Join</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @php
        $tab = request('tab', 'active');
        $showArchived = $tab === 'archived';
    @endphp

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ !$showArchived ? 'active' : '' }}" href="{{ route('lists.index') }}">My lists <span class="badge bg-secondary ms-1">{{ $activeLists->count() }}</span></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $showArchived ? 'active' : '' }}" href="{{ route('lists.index', ['tab' => 'archived']) }}">Archived <span class="badge bg-secondary ms-1">{{ $archivedLists->count() }}</span></a>
        </li>
    </ul>

    @if (!$showArchived)
        @if ($activeLists->isEmpty())
            <div class="card sharecart-card text-center py-5 px-4">
                <div class="card-body py-5">
                    <div class="mb-3 opacity-75">📋</div>
                    <h5 class="text-dark mb-2">No lists yet</h5>
                    <p class="text-muted mb-4">Create your first list and start adding items.</p>
                    <a href="{{ route('lists.create') }}" class="btn btn-primary">Create your first list</a>
                </div>
            </div>
        @else
            <div class="row g-3 lists-page">
                @foreach ($activeLists as $list)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card sharecart-card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1">
                                    <i class="bi {{ $list->icon }} me-1 text-primary"></i>
                                    <a href="{{ route('lists.show', $list) }}" class="text-decoration-none">{{ $list->name }}</a>
                                </h5>
                                <p class="card-text text-muted small mb-3">
                                    {{ $list->items_count }} {{ Str::plural('item', $list->items_count) }}
                                    @if ($list->due_date)
                                        <span class="d-block mt-1">
                                            <span class="text-primary">📅</span> {{ $list->due_date->format('M j, Y') }}
                                        </span>
                                    @endif
                                </p>
                                <div class="mt-auto d-flex gap-2 flex-wrap">
                                    <a href="{{ route('lists.show', $list) }}" class="btn btn-sm btn-primary">Open</a>
                                    <a href="{{ route('lists.edit', $list) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('lists.archive', $list) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Archive this list?" data-swal-text="You can restore it later from the Archived tab." data-swal-confirm-text="Archive">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">Archive</button>
                                    </form>
                                    @can('delete', $list)
                                        <form action="{{ route('lists.destroy', $list) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Delete this list?" data-swal-text="This list and all its items will be permanently deleted. This cannot be undone." data-swal-confirm-text="Yes, delete it">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        @if ($archivedLists->isEmpty())
            <div class="card sharecart-card text-center py-5 px-4">
                <div class="card-body py-5">
                    <p class="text-muted mb-0">No archived lists. Archive a list from My lists to see it here.</p>
                </div>
            </div>
        @else
            <div class="row g-3 lists-page">
                @foreach ($archivedLists as $list)
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card sharecart-card h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1 text-muted">
                                    <i class="bi {{ $list->icon }} me-1"></i>
                                    {{ $list->name }}
                                </h5>
                                <p class="card-text text-muted small mb-3">
                                    {{ $list->items_count }} {{ Str::plural('item', $list->items_count) }}
                                    @if ($list->due_date)
                                        <span class="d-block mt-1">📅 {{ $list->due_date->format('M j, Y') }}</span>
                                    @endif
                                </p>
                                <div class="mt-auto d-flex gap-2 flex-wrap">
                                    <form action="{{ route('lists.restore', $list) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Restore</button>
                                    </form>
                                    <a href="{{ route('lists.show', $list) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                    <form action="{{ route('lists.destroy', $list) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Delete this list?" data-swal-text="This list and all its items will be permanently deleted. This cannot be undone." data-swal-confirm-text="Yes, delete it">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</x-app-layout>
