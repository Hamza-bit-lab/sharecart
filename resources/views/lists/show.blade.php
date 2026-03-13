<x-app-layout>
    @php
        $title = $list->name;
    @endphp
    @push('head')
    <style>
        .hover-elevate { transition: all 0.2s ease-in-out; }
        .hover-elevate:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important; }
        .avatar-sm, .avatar-xs { flex-shrink: 0; }
        .very-small { font-size: 0.7rem; }
        .sharecart-card { border-radius: 1rem; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .breadcrumb-item + .breadcrumb-item::before { content: "•"; color: #adb5bd; }
        .hvr-bg-primary:hover { background-color: var(--bs-primary); color: white !important; }
        .hvr-bg-primary:hover .badge { background-color: white !important; color: var(--bs-primary) !important; }
        .hover-bg-danger-soft:hover { background-color: #fee2e2 !important; color: #dc2626 !important; }
        .section-header:hover { background-color: #f1f5f9 !important; }
        .list-item-card { transition: all 0.2s; border: 1px solid #e2e8f0 !important; }
        .list-item-card:hover { border-color: var(--bs-primary) !important; background-color: #f8fafc; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1); border-color: #86b7fe; }
    </style>
    @endpush
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 py-2">
            <div>
                <nav aria-label="breadcrumb" class="mb-1">
                    <ol class="breadcrumb mb-0 small uppercase fw-bold" style="letter-spacing: 1px;">
                        <li class="breadcrumb-item"><a href="{{ route('lists.index') }}" class="text-decoration-none text-muted">My Lists</a></li>
                        <li class="breadcrumb-item active text-primary" aria-current="page">List Details</li>
                    </ol>
                </nav>
                <h1 class="display-6 fw-bold mb-0 text-dark">
                    <i class="bi {{ $list->icon }} me-2 text-primary"></i>{{ $list->name }}
                </h1>
                @if ($list->due_date)
                    <p class="text-muted small mb-0 mt-2 d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border p-2">
                            <i class="bi bi-calendar3 me-1"></i> {{ $list->due_date->format('l, F j, Y') }}
                        </span>
                    </p>
                @endif
            </div>
            <div class="d-flex gap-2">
                @auth
                    @can('update', $list)
                        <a href="{{ route('lists.edit', $list) }}" class="btn btn-white shadow-sm border btn-sm px-3 py-2 rounded-pill hover-elevate">
                            <i class="bi bi-pencil-square me-1"></i> Edit List
                        </a>
                    @endcan
                @endauth
                <a href="{{ auth()->check() ? route('lists.index') : route('join') }}" class="btn btn-dark shadow-sm btn-sm px-3 py-2 rounded-pill hover-elevate">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4 order-xl-2">
            @php
                $guestCollaborators = $guestCollaborators ?? collect();
                $collaboratorCount = $list->sharedWith->count() + $guestCollaborators->count();
            @endphp
            <div class="card sharecart-card shadow-sm border-0 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Collaborators</h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-white text-primary rounded-pill">{{ $collaboratorCount }}</span>
                            <form action="{{ route('lists.ping', $list) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning border-0 rounded-circle p-1 hover-elevate" title="Nudge collaborators">
                                    <i class="bi bi-megaphone-fill" style="font-size: 0.8rem;"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        @if ($collaboratorCount === 0)
                            <div class="text-center py-3 bg-light rounded-3 mb-3 border border-dashed">
                                <p class="small text-muted mb-0">Not shared yet — add someone below</p>
                            </div>
                        @else
                            <div class="collaborator-list mb-3">
                                @foreach ($list->sharedWith as $sharedUser)
                                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($sharedUser->name, 0, 1)) }}
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="small fw-semibold">{{ $sharedUser->name }}</span>
                                                <span class="very-small text-muted text-truncate" style="max-width: 120px;">{{ $sharedUser->email }}</span>
                                            </div>
                                        </div>
                                        <form action="{{ route('lists.unshare', [$list, $sharedUser]) }}" method="POST" class="swal-confirm" data-swal-title="Remove access?" data-swal-text="{{ $sharedUser->email }} will lose access.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 text-decoration-none small">Remove</button>
                                        </form>
                                    </div>
                                @endforeach
                                @foreach ($guestCollaborators as $name)
                                    <div class="d-flex align-items-center py-2 border-bottom border-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-xs bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($name, 0, 1)) }}
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="small fw-semibold">{{ $name }}</span>
                                                <span class="very-small text-muted">Guest User</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            @can('share', $list)
            <div class="card sharecart-card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-info"></i>Invite Someone</h6>
                </div>
                <div class="card-body p-4">
                        <form action="{{ route('lists.share', $list) }}" method="POST" class="mb-4">
                            @csrf
                            <label for="share-email" class="form-label small fw-bold text-muted">Invite via Email</label>
                            <div class="input-group input-group-sm mb-2 shadow-none">
                                <input type="email" name="email" id="share-email" class="form-control border-end-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="colleague@example.com" required>
                                <button type="submit" class="btn btn-primary border-start-0 px-3">Invite</button>
                            </div>
                            @error('email')
                                <div class="text-danger very-small mt-1">{{ $message }}</div>
                            @enderror
                        </form>

                        @if (!empty($joinCode))
                        <div class="mb-4 bg-light p-3 rounded-3 border">
                            <label class="form-label small fw-bold text-muted d-block mb-2">Join by Code</label>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold fs-4 font-monospace text-primary" id="join-code-display">{{ $joinCode }}</span>
                                <button type="button" class="btn btn-white btn-sm shadow-sm border rounded-pill px-3" id="copy-join-code">Copy</button>
                            </div>
                        </div>
                        @endif

                        @if (!empty($inviteLink))
                        <div>
                            <label class="form-label small fw-bold text-muted d-block mb-2">Invite Link</label>
                            <div class="d-flex gap-2">
                                <input type="hidden" id="invite-link-input" value="{{ $inviteLink }}">
                                <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 rounded-pill" id="copy-invite-link">Copy Link</button>
                                <a href="https://wa.me/?text={{ urlencode('Join my list: ' . $inviteLink) }}" class="btn btn-success btn-sm p-2 rounded-circle d-flex align-items-center justify-content-center" target="_blank" rel="noopener" style="width: 31px; height: 31px;">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endcan
        </div>

        <div class="col-12 col-xl-8 order-xl-1">
            <div class="card sharecart-card mb-4 shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-dotted me-2 text-primary"></i>Add New Item</h6>
                    @if($templates->isNotEmpty())
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary border-0 text-white rounded-pill px-3 dropdown-toggle shadow-none" type="button" data-bs-toggle="dropdown">
                            ✨ Magic Templates
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                            <li><h6 class="dropdown-header text-muted uppercase small fw-bold">Select Template</h6></li>
                            @foreach($templates as $template)
                            <li>
                                <form action="{{ route('templates.apply', [$template, $list]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex justify-content-between align-items-center py-2 hvr-bg-primary">
                                        <span>{{ $template->name }}</span>
                                        <span class="badge bg-light text-primary rounded-pill border">{{ $template->items_count }}</span>
                                    </button>
                                </form>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('lists.items.store', $list) }}" method="POST" class="row g-3 align-items-end" id="add-item-form">
                        @csrf
                        <div class="col-12 col-md-7">
                            <label for="item-name" class="form-label small fw-bold text-muted">What do you need?</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="name" id="item-name" class="form-control border-start-0 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Fresh Milk, Avocados" maxlength="255" autocomplete="off">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="item-quantity" class="form-label small fw-bold text-muted">Qty</label>
                            <div class="input-group">
                                <input type="number" name="quantity" id="item-quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" max="9999">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm hover-elevate">
                                <i class="bi bi-plus-lg me-1"></i> Add
                            </button>
                        </div>
                    </form>
                    <div class="mt-4 pt-3 border-top border-light">
                        <div id="suggestion-badges" class="d-flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>

    <!-- Items list (refreshed by polling for real-time sync) -->
    <div class="card sharecart-card" data-sort="{{ $sort ?? 'section' }}">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 border-0">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check me-2 text-primary"></i>Shopping List</h6>
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
                <button type="submit" class="btn btn-sm btn-outline-success border-success text-success fw-bold px-3 rounded-pill hvr-bg-success">Reset for next trip</button>
            </form>
        </div>
        <div class="card-body p-0" id="list-items-container" data-sort="{{ $sort ?? 'section' }}">
            @include('lists.partials.items-by-section', ['groupedItems' => $groupedItems ?? []])
        </div>
    </div>
</div> {{-- End col-xl-8 --}}
</div> {{-- End row --}}

    <!-- Payments Section -->
    <div class="card sharecart-card mb-5 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 text-success fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Payments & Expenses</h6>
                </div>
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-bold border border-success border-opacity-10">
                    Total: {{ number_format($list->payments->sum('amount'), 2) }} {{ $list->payments->first()->currency ?? 'EUR' }}
                </span>
            </div>
        </div>
        <div class="card-body p-4 bg-light bg-opacity-10">
            <!-- Add Payment Form -->
            <form action="{{ route('lists.payments.store', $list) }}" method="POST" class="row g-3 align-items-end mb-4">
                @csrf
                <div class="col-12 col-md-5">
                    <label for="payment-amount" class="form-label small fw-semibold text-muted">Amount & Currency</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-cash"></i></span>
                        <input type="number" name="amount" id="payment-amount" class="form-control border-start-0" step="0.01" min="0.01" placeholder="0.00" required>
                        <select name="currency" class="form-select bg-light border-start-0" style="max-width: 100px;">
                            <option value="EUR">EUR</option>
                            <option value="USD">USD</option>
                            <option value="GBP">GBP</option>
                            <option value="PKR">PKR</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label for="payment-date" class="form-label small fw-semibold text-muted">Date</label>
                    <input type="date" name="paid_at" id="payment-date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm hover-elevate">
                        Save Payment
                    </button>
                </div>
            </form>

            <!-- Settlement Stats -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="bg-white p-3 rounded-4 border-0 shadow-sm h-100 mb-2 mb-md-0" style="background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);">
                        <h6 class="very-small fw-bold text-muted text-uppercase mb-3" style="letter-spacing: 0.5px;"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Spending Analysis</h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Grand Total:</span>
                            <span class="fw-bold text-dark fs-5">{{ number_format($totalSpent, 2) }} <small class="text-muted">{{ $list->payments->first()->currency ?? 'PKR' }}</small></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-light">
                            <span class="small text-muted">Fair Share / Person:</span>
                            <span class="fw-bold text-primary">{{ number_format($fairShare, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="bg-white p-3 rounded-4 border-0 shadow-sm h-100 overflow-auto" style="max-height: 140px; background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);">
                        <h6 class="very-small fw-bold text-muted text-uppercase mb-3" style="letter-spacing: 0.5px;"><i class="bi bi-arrow-left-right me-2 text-warning"></i>Who Owes Whom?</h6>
                        <div class="settlement-list">
                        @foreach($participants as $p)
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-light last-child-border-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-xs bg-light rounded-circle text-center fw-bold text-muted" style="width: 20px; height: 20px; font-size: 0.6rem; line-height: 20px;">
                                        {{ strtoupper(substr($p['name'], 0, 1)) }}
                                    </div>
                                    <span class="very-small fw-semibold text-dark">{{ $p['name'] }}</span>
                                </div>
                                @if($p['balance'] > 0)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10 p-2 rounded-3" style="font-size: 0.65rem;">+ {{ number_format($p['balance'], 2) }}</span>
                                @elseif($p['balance'] < 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-10 p-2 rounded-3" style="font-size: 0.65rem;">- {{ number_format(abs($p['balance']), 2) }}</span>
                                @else
                                    <span class="text-muted very-small">Settled ✅</span>
                                @endif
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @if($list->payments->isEmpty())
                <div class="text-center py-4 bg-white rounded-3 border border-dashed">
                    <div class="text-muted mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-receipt text-opacity-25" viewBox="0 0 16 16">
                          <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0L11 1.293l.646-.647a.5.5 0 0 1 .708 0L13 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647a.5.5 0 0 1 .14.434v12.988a.5.5 0 0 1-.14.434l-.646.647a.5.5 0 0 1-.708 0L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.434-.14L1.5 14.071V1.506zM3 2.5v11.5l.5-.5.5.5.5-.5.5.5.5-.5.5.5.5-.5.5.5.5-.5.5.5.5-.5.5.5V2.5l-.5.5-.5-.5-.5.5-.5-.5-.5.5-.5-.5-.5.5-.5-.5-.5.5-.5-.5-.5.5-.5-.5z"/>
                        </svg>
                    </div>
                    <p class="text-muted small">No payments recorded yet.</p>
                </div>
            @else
                <div class="table-responsive rounded-3 border bg-white overflow-hidden shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small fw-bold text-uppercase text-muted border-0">Payer</th>
                                <th class="py-3 small fw-bold text-uppercase text-muted border-0 text-end">Amount</th>
                                <th class="py-3 small fw-bold text-uppercase text-muted border-0 text-center">Date</th>
                                <th class="px-4 py-3 small fw-bold text-uppercase text-muted border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($list->payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 border-0">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                {{ strtoupper(substr($payment->user ? $payment->user->name : $payment->guest_name, 0, 1)) }}
                                            </div>
                                            <span class="fw-medium">{{ $payment->user ? $payment->user->name : $payment->guest_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 border-0 text-end">
                                        <span class="fw-bold text-dark">{{ number_format($payment->amount, 2) }}</span>
                                        <span class="small text-muted ms-1">{{ $payment->currency }}</span>
                                    </td>
                                    <td class="py-3 border-0 text-center text-muted small">
                                        {{ $payment->paid_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 border-0 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary border-0 hover-bg-primary p-2 rounded-circle edit-payment-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPaymentModal"
                                                    data-id="{{ $payment->id }}"
                                                    data-amount="{{ $payment->amount }}"
                                                    data-currency="{{ $payment->currency }}"
                                                    data-date="{{ $payment->paid_at->format('Y-m-d') }}"
                                                    title="Edit payment">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <form action="{{ route('lists.payments.destroy', [$list, $payment]) }}" method="POST" class="d-inline swal-confirm" data-swal-title="Delete payment record?" data-swal-text="This cannot be undone.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 hover-bg-danger-soft p-2 rounded-circle" title="Delete record">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="editPaymentModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-payment-form" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label for="edit-payment-amount" class="form-label small fw-bold text-muted">Amount & Currency</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-cash"></i></span>
                                <input type="number" name="amount" id="edit-payment-amount" class="form-control border-start-0" step="0.01" min="0.01" required>
                                <select name="currency" id="edit-payment-currency" class="form-select bg-light border-start-0" style="max-width: 100px;">
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                    <option value="GBP">GBP</option>
                                    <option value="PKR">PKR</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="edit-payment-date" class="form-label small fw-bold text-muted">Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="paid_at" id="edit-payment-date" class="form-control border-start-0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">Update Changes</button>
                    </div>
                </form>
            </div>
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
        var lastPingAt = @json($list->last_ping_at ? $list->last_ping_at->toIso8601String() : null);

        function poll() {
            fetch(pollUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var listUpdated = data.list.updated_at;
                var currentPingAt = data.list.last_ping_at;
                
                // Detection of new ping
                if (currentPingAt && currentPingAt !== lastPingAt) {
                    lastPingAt = currentPingAt;
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Nudge!',
                            text: data.list.last_ping_by_name + ' is looking at the list!',
                            showConfirmButton: false,
                            timer: 4000,
                            timerProgressBar: true
                        });
                    }
                }

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
                container.innerHTML = '<div class="p-5 text-center bg-light rounded-4 border-2 border-dashed m-3"><div class="display-6 text-muted-opacity-25 mb-2"><i class="bi bi-cart-x"></i></div><p class="text-muted mb-0">Your list is currently empty.</p><p class="small text-muted">Add some items above to get started!</p></div>';
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
                    html += '<div class="border-bottom-0 mb-3" data-section="' + escapeHtml(sectionKey) + '">';
                    html += '<div class="section-header px-4 py-3 bg-white d-flex align-items-center justify-content-between cursor-pointer sticky-top shadow-sm" style="top: 0; z-index: 10;" data-bs-toggle="collapse" data-bs-target="#' + sectionId + '" aria-expanded="true" aria-controls="' + sectionId + '" role="button" tabindex="0">';
                    html += '<div class="d-flex align-items-center gap-3"><div class="section-icon text-primary"><i class="bi bi-tag-fill"></i></div><span class="fw-bold text-dark fs-6">' + escapeHtml(sectionLabel) + '</span></div>';
                    html += '<div class="d-flex align-items-center gap-2"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 px-3 py-2 rounded-pill">' + sectionItems.length + ' items</span><i class="bi bi-chevron-down small text-muted"></i></div></div>';
                    html += '<div class="collapse show" id="' + sectionId + '"><div class="row g-3 p-4 list-items-grid bg-light bg-opacity-25">';
                    sectionItems.forEach(function (item) {
                        var checked = item.completed ? ' checked' : '';
                        var cardClass = item.completed ? 'opacity-75 bg-light grayscale' : 'shadow-sm bg-white';
                        var textClass = item.completed ? ' text-decoration-line-through text-muted fw-normal' : 'fw-semibold text-dark';
                        var itemUrl = baseUrl + '/items/' + item.id;
                        var purchasedByName = null;
                        if (item.completed && item.completed_by && item.completed_by.name) {
                            purchasedByName = item.completed_by.name;
                        } else if (item.completed && item.completed_by_name) {
                            purchasedByName = item.completed_by_name;
                        }
                        var purchasedBy = purchasedByName ? '<div class="d-flex align-items-center gap-1 mt-1"><i class="bi bi-person-check very-small text-success"></i><span class="very-small text-success fw-medium">by ' + escapeHtml(purchasedByName) + '</span></div>' : '';
                        
                        html += '<div class="col-12 col-md-6 col-lg-4" data-item-id="' + item.id + '">';
                        html += '<div class="list-item-card border rounded-4 p-3 h-100 d-flex align-items-center justify-content-between gap-3 ' + cardClass + '">';
                        html += '<form action="' + itemUrl + '" method="POST" class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">';
                        html += '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="PUT">';
                        html += '<input type="hidden" name="completed" value="' + (item.completed ? '0' : '1') + '">';
                        html += '<div class="form-check m-0"><input type="checkbox" class="form-check-input item-toggle flex-shrink-0" style="width: 1.5rem; height: 1.5rem; cursor: pointer;" ' + checked + ' title="Mark as done"></div>';
                        html += '<div class="min-w-0 flex-grow-1"><span class="d-block text-truncate ' + textClass + '" style="font-size: 0.95rem;">' + escapeHtml(item.name) + '</span>';
                        if (item.quantity > 1) html += '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill very-small px-2 py-1 mt-1">Qty: ' + item.quantity + '</span>';
                        html += purchasedBy + '</div></form>';
                        html += '<form action="' + itemUrl + '" method="POST" class="d-inline flex-shrink-0 swal-confirm" data-swal-title="Remove this item?" data-swal-text="This item will be removed from the list.">';
                        html += '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="DELETE">';
                        html += '<button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-2 hover-bg-danger-soft" title="Remove Item"><i class="bi bi-trash3"></i></button></form>';
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
    // Edit Payment Modal Logic
    document.addEventListener('DOMContentLoaded', function () {
        var editPaymentModal = document.getElementById('editPaymentModal');
        if (editPaymentModal) {
            editPaymentModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var id = button.getAttribute('data-id');
                var amount = button.getAttribute('data-amount');
                var currency = button.getAttribute('data-currency');
                var date = button.getAttribute('data-date');
                
                var form = editPaymentModal.querySelector('#edit-payment-form');
                var amountInput = editPaymentModal.querySelector('#edit-payment-amount');
                var currencySelect = editPaymentModal.querySelector('#edit-payment-currency');
                var dateInput = editPaymentModal.querySelector('#edit-payment-date');
                
                // Construct the update URL
                var currentUrl = window.location.pathname;
                var listIdMatch = currentUrl.match(/\/lists\/(\d+)/);
                var listId = listIdMatch ? listIdMatch[1] : '';
                
                form.action = '/lists/' + listId + '/payments/' + id;
                amountInput.value = amount;
                currencySelect.value = currency;
                dateInput.value = date;
            });
        }
    });
    </script>
    @endpush
</x-app-layout>
