<x-app-layout>
    @php
        $title = $list->name;
    @endphp
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h1 class="h2 mb-0">{{ $list->name }}</h1>
                @if ($list->due_date)
                    <p class="text-muted small mb-0 mt-1">📅 Shopping date: {{ $list->due_date->format('l, F j, Y') }}</p>
                @endif
            </div>
            <div class="d-flex gap-2">
                <a href="{{ auth()->check() ? route('lists.index') : route('join') }}" class="btn btn-outline-secondary btn-sm">← Back</a>
                @auth
                    @can('update', $list)
                        <a href="{{ route('lists.edit', $list) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                    @endcan
                @endauth
            </div>
        </div>
    </x-slot>

    @can('share', $list)
    @php
        $guestCollaborators = $guestCollaborators ?? collect();
        $collaboratorCount = $list->sharedWith->count() + $guestCollaborators->count();
    @endphp
    <div class="card sharecart-card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h6 class="mb-0">
                    Collaborators
                    <span class="badge bg-primary ms-1">{{ $collaboratorCount }}</span>
                </h6>
                <span class="small text-muted">
                    @if ($collaboratorCount === 0)
                        Not shared yet — add someone below
                    @else
                        {{ $collaboratorCount }} {{ Str::plural('person', $collaboratorCount) }} can edit this list
                    @endif
                </span>
            </div>

            @if ($list->sharedWith->isNotEmpty())
                <p class="small fw-semibold text-muted mb-1">By email (logged in)</p>
                <ul class="list-group list-group-flush mb-3">
                    @foreach ($list->sharedWith as $sharedUser)
                        <li class="list-group-item d-flex align-items-center justify-content-between">
                            <span>{{ $sharedUser->name }} <small class="text-muted">({{ $sharedUser->email }})</small></span>
                            <form action="{{ route('lists.unshare', [$list, $sharedUser]) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Remove collaborator?" data-swal-text="{{ $sharedUser->email }} will lose access to this list.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($guestCollaborators->isNotEmpty())
                <p class="small fw-semibold text-muted mb-1">Joined by code</p>
                <ul class="list-group list-group-flush mb-3">
                    @foreach ($guestCollaborators as $name)
                        <li class="list-group-item d-flex align-items-center">
                            <span>{{ $name }}</span>
                            <span class="small text-muted ms-2">(no account)</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="pt-2 border-top">
                <label for="share-email" class="form-label small fw-semibold">Add another collaborator</label>
                <form action="{{ route('lists.share', $list) }}" method="POST" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-12 col-md-4">
                        <input type="email" name="email" id="share-email" class="form-control form-control-sm @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter their email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
                    </div>
                </form>
            </div>

            @if (!empty($joinCode))
            <div class="pt-3 mt-3 border-top">
                <label class="form-label small fw-semibold">Join by code (no login)</label>
                <p class="small text-muted mb-2">Share this 5-digit code. Anyone can enter it at <a href="{{ route('join') }}">Join by code</a> to view and edit without an account.</p>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="fw-bold fs-5 font-monospace bg-light rounded px-3 py-2" id="join-code-display">{{ $joinCode }}</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="copy-join-code" title="Copy code">Copy code</button>
                </div>
            </div>
            @endif
            @if (!empty($inviteLink))
            <div class="pt-3 mt-3 border-top">
                <label class="form-label small fw-semibold">Share via link (requires login)</label>
                <p class="small text-muted mb-2">Anyone with this link can join after signing in or registering.</p>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="text" class="form-control form-control-sm flex-grow-1" style="max-width: 20rem;" id="invite-link-input" value="{{ $inviteLink }}" readonly>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="copy-invite-link" title="Copy link">Copy link</button>
                    <a href="https://wa.me/?text={{ urlencode('Join my list: ' . $inviteLink) }}" class="btn btn-outline-success btn-sm" target="_blank" rel="noopener" title="Share on WhatsApp">WhatsApp</a>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endcan

    @guest
        @if (empty($guestName ?? null))
            <!-- Guest name modal (shown once after joining by code) -->
            <div class="modal fade" id="guestNameModal" tabindex="-1" aria-labelledby="guestNameModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="guestNameModalLabel">Who is using this list?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('lists.guest-name', $list) }}">
                            @csrf
                            <div class="modal-body">
                                <p class="small text-muted mb-3">Enter a name so others can see who marked items as done. This is only for this browser.</p>
                                <div class="mb-3">
                                    <label for="guest-name-input" class="form-label small fw-semibold">Your name</label>
                                    <input type="text" name="name" id="guest-name-input" class="form-control" maxlength="50" required placeholder="e.g. Ali, Hamza">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save name</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endguest

    <!-- Add item form -->
    <div class="card sharecart-card mb-4">
        <div class="card-body">
            <form action="{{ route('lists.items.store', $list) }}" method="POST" class="row g-2 align-items-end" id="add-item-form">
                @csrf
                <div class="col-12 col-md-5">
                    <label for="item-name" class="form-label small">Item name</label>
                    <input type="text" name="name" id="item-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Milk, face wash" maxlength="255" autocomplete="off">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 col-md-2">
                    <label for="item-quantity" class="form-label small">Qty</label>
                    <input type="number" name="quantity" id="item-quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" max="9999">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <button type="submit" class="btn btn-primary w-100">Add item</button>
                </div>
            </form>
            <div class="mt-3">
                <p class="small text-muted mb-2">Suggestions update as you type (e.g. Mobile → Charger, Case). Click a badge to add:</p>
                <div id="suggestion-badges" class="d-flex flex-wrap gap-2"></div>
            </div>
        </div>
    </div>

    <!-- Items list (refreshed by polling for real-time sync) -->
    <div class="card sharecart-card" data-sort="{{ $sort ?? 'section' }}">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <span>Items</span>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <label class="mb-0 small text-muted">Sort:</label>
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('lists.show', [$list, 'sort' => 'section']) }}" class="btn btn-outline-secondary {{ ($sort ?? 'section') === 'section' ? 'active' : '' }}">By section</a>
                    <a href="{{ route('lists.show', [$list, 'sort' => 'name']) }}" class="btn btn-outline-secondary {{ ($sort ?? '') === 'name' ? 'active' : '' }}">By name</a>
                    <a href="{{ route('lists.show', [$list, 'sort' => 'date']) }}" class="btn btn-outline-secondary {{ ($sort ?? '') === 'date' ? 'active' : '' }}">By date added</a>
                </div>
                <span class="badge bg-secondary" id="items-count">{{ $list->items->count() }}</span>
            </div>
        </div>
        <div id="all-done-banner" class="alert alert-success rounded-0 mb-0 border-0 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-3 {{ ($allItemsPurchased ?? false) ? '' : 'd-none' }}">
            <span class="fw-semibold">All items purchased! Great job.</span>
            <form action="{{ route('lists.reset-items', $list) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success">Reset for next trip</button>
            </form>
        </div>
        <div class="card-body p-0" id="list-items-container" data-sort="{{ $sort ?? 'section' }}">
            @include('lists.partials.items-by-section', ['groupedItems' => $groupedItems ?? []])
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        @guest
        @if (empty($guestName ?? null))
        document.addEventListener('DOMContentLoaded', function () {
            var modalEl = document.getElementById('guestNameModal');
            if (modalEl && window.bootstrap) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
        @endif
        @endguest

        var sortParam = @json($sort ?? 'section');
        var pollUrl = @json(route('lists.poll', $list)) + (sortParam ? '?sort=' + encodeURIComponent(sortParam) : '');
        var listId = @json($list->id);
        var baseUrl = (function () {
            try {
                return new URL(pollUrl).pathname.replace(/\/poll$/, '');
            } catch (e) {
                return pollUrl.replace(/\/poll(\?.*)?$/, '');
            }
        })();
        var container = document.getElementById('list-items-container');
        var countEl = document.getElementById('items-count');
        var lastUpdated = @json($list->updated_at->toIso8601String());
        var itemUpdatedAt = @json($list->items->max('updated_at')?->toIso8601String() ?? $list->updated_at->toIso8601String());

        function poll() {
            fetch(pollUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var listUpdated = data.list.updated_at;
                var items = data.items || [];
                var maxItemUpdated = items.length ? items.reduce(function (acc, i) {
                    return (i.updated_at > acc) ? i.updated_at : acc;
                }, '') : listUpdated;
                if (listUpdated !== lastUpdated || maxItemUpdated !== itemUpdatedAt) {
                    lastUpdated = listUpdated;
                    itemUpdatedAt = maxItemUpdated;
                    renderItems(items, data.section_labels, data.section_order);
                }
                var allDone = items.length > 0 && items.every(function (i) { return i.completed === true || i.completed === 1; });
                var banner = document.getElementById('all-done-banner');
                if (banner) { banner.classList.toggle('d-none', !allDone); }
            })
            .catch(function () {});
        }

        function renderItems(items, sectionLabels, sectionOrder) {
            sectionLabels = sectionLabels || {};
            sectionOrder = sectionOrder || ['fruits', 'vegetables', 'cooking', 'beauty', 'dairy_bakery', 'beverages', 'snacks', 'electronics', 'other'];
            if (items.length === 0) {
                container.innerHTML = '<div class="p-4 text-muted text-center">No items yet. Add one above.</div>';
            } else {
                var bySection = {};
                items.forEach(function (item) {
                    var sec = item.section || 'other';
                    if (!bySection[sec]) bySection[sec] = [];
                    bySection[sec].push(item);
                });
                var csrf = document.querySelector('meta[name="csrf-token"]').content;
                var html = '';
                sectionOrder.forEach(function (sectionKey) {
                    var sectionItems = bySection[sectionKey];
                    if (!sectionItems || sectionItems.length === 0) return;
                    var sectionLabel = sectionLabels[sectionKey] || sectionKey;
                    var sectionId = 'section-' + sectionKey;
                    html += '<div class="border-bottom border-end-0 border-start-0 border-top-0" data-section="' + escapeHtml(sectionKey) + '">';
                    html += '<div class="section-header px-3 py-3 bg-light d-flex align-items-center justify-content-between border-bottom" data-bs-toggle="collapse" data-bs-target="#' + sectionId + '" aria-expanded="true" aria-controls="' + sectionId + '" role="button" tabindex="0" style="cursor:pointer;">';
                    html += '<span class="d-flex align-items-center gap-2"><span class="section-toggle-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M6.22 4.22a.75.75 0 0 1 1.06 0l3.25 3.25a.75.75 0 0 1 0 1.06l-3.25 3.25a.75.75 0 0 1-1.06-1.06L8.94 8 6.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg></span><span class="fw-semibold text-dark">' + escapeHtml(sectionLabel) + '</span></span>';
                    html += '<span class="badge bg-secondary rounded-pill">' + sectionItems.length + '</span></div>';
                    html += '<div class="collapse show" id="' + sectionId + '"><div class="row g-2 p-2 list-items-grid">';
                    sectionItems.forEach(function (item) {
                        var checked = item.completed ? ' checked' : '';
                        var textClass = item.completed ? ' text-decoration-line-through text-muted' : '';
                        var itemUrl = baseUrl + '/items/' + item.id;
                        var purchasedByName = null;
                        if (item.completed && item.completed_by && item.completed_by.name) {
                            purchasedByName = item.completed_by.name;
                        } else if (item.completed && item.completed_by_name) {
                            purchasedByName = item.completed_by_name;
                        }
                        var purchasedBy = purchasedByName ? '<span class="d-block very-small text-muted">(by ' + escapeHtml(purchasedByName) + ')</span>' : '';
                        html += '<div class="col-6 col-md-6" data-item-id="' + item.id + '">';
                        html += '<div class="list-item-card border rounded p-2 h-100 d-flex align-items-center justify-content-between gap-2">';
                        html += '<form action="' + itemUrl + '" method="POST" class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">';
                        html += '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="PUT">';
                        html += '<input type="hidden" name="completed" value="' + (item.completed ? '0' : '1') + '">';
                        html += '<input type="checkbox" class="form-check-input item-toggle flex-shrink-0" ' + checked + ' title="Toggle purchased">';
                        html += '<span class="text-truncate small' + textClass + '">' + escapeHtml(item.name) + (item.quantity > 1 ? ' × ' + item.quantity : '') + purchasedBy + '</span></form>';
                        html += '<form action="' + itemUrl + '" method="POST" class="d-inline flex-shrink-0 swal-confirm" data-swal-title="Remove this item?" data-swal-text="This item will be removed from the list.">';
                        html += '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="DELETE">';
                        html += '<button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Remove">×</button></form>';
                        html += '</div></div>';
                    });
                    html += '</div></div></div>';
                });
                container.innerHTML = html;
                container.querySelectorAll('.item-toggle').forEach(function (el) {
                    el.addEventListener('change', function () { this.closest('form').submit(); });
                });
            }
            if (countEl) countEl.textContent = items.length;
        }

        function escapeHtml(s) {
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        setInterval(poll, 3000);
    })();

    // Suggestion badges: category-based (beauty, cooking, etc.). Context = current list items so suggestions match (e.g. lotion → shampoo, face wash; cooking oil → flour, spices).
    (function () {
        var form = document.getElementById('add-item-form');
        var input = document.getElementById('item-name');
        var quantityInput = document.getElementById('item-quantity');
        var container = document.getElementById('suggestion-badges');
        if (!form || !input || !container) return;
        var suggestionsUrl = @json(route('suggestions.index'));
        var listItemNames = @json($list->items->pluck('name')->values()->all());

        function addItemByName(name) {
            input.value = name;
            if (quantityInput) quantityInput.value = 1;
            form.submit();
        }

        function setLoading(loading) {
            if (loading) container.innerHTML = '<span class="small text-muted">Suggestions loading…</span>';
        }

        function renderBadges(suggestions) {
            container.innerHTML = '';
            (suggestions || []).forEach(function (s) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-primary';
                btn.textContent = s.name;
                btn.title = 'Add ' + s.name;
                btn.addEventListener('click', function () { addItemByName(s.name); });
                container.appendChild(btn);
            });
        }

        function fetchAndShow(q) {
            setLoading(true);
            var url = suggestionsUrl + '?limit=6';
            if (q) url += '&q=' + encodeURIComponent(q);
            if (listItemNames.length) url += '&context=' + encodeURIComponent(listItemNames.join(','));
            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) { renderBadges((data.data && data.data.suggestions) ? data.data.suggestions : (data.suggestions || [])); })
                .catch(function () { renderBadges([]); });
        }

        // On load: show suggestions based on current list (e.g. if list has lotion → beauty suggestions)
        fetchAndShow('');

        var timeout;
        input.addEventListener('input', function () {
            clearTimeout(timeout);
            var q = this.value.trim();
            timeout = setTimeout(function () { fetchAndShow(q); }, 200);
        });
    })();

    // Copy join code to clipboard
    (function () {
        var btn = document.getElementById('copy-join-code');
        var el = document.getElementById('join-code-display');
        if (!btn || !el) return;
        btn.addEventListener('click', function () {
            try {
                navigator.clipboard.writeText(el.textContent.trim());
                var orig = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function () { btn.textContent = orig; }, 2000);
            } catch (e) {
                document.execCommand('copy');
            }
        });
    })();
    // Copy invite link to clipboard
    (function () {
        var btn = document.getElementById('copy-invite-link');
        var input = document.getElementById('invite-link-input');
        if (!btn || !input) return;
        btn.addEventListener('click', function () {
            input.select();
            input.setSelectionRange(0, 99999);
            try {
                navigator.clipboard.writeText(input.value);
                var orig = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(function () { btn.textContent = orig; }, 2000);
            } catch (e) {
                document.execCommand('copy');
            }
        });
    })();
    </script>
    @endpush
</x-app-layout>
