@extends('layouts.app')

@section('title', 'Accounts - Nexus ERP')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('accounts_create');
    $canEdit = auth()->user()->hasPermission('accounts_edit');
    $canDelete = auth()->user()->hasPermission('accounts_delete');
    $currency = auth()->user()->currentCompany->currency ?? 'USD';
@endphp

<!-- Top Header & Summary -->
<div class="row mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-lg bg-label-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="bx bx-wallet text-primary fs-2"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Liquid Treasury</small>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($totalLiquidity ?? $accounts->sum('current_balance'), 2) }}</h3>
                    <small class="text-muted">{{ $accounts->count() }} Active Accounts</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-md-end gap-2">
        @if($accounts->count() > 1)
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="bx bx-transfer-alt me-1"></i> Internal Transfer
            </button>
        @endif
        @if($canCreate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                <i class="bx bx-plus me-1"></i> Add Account
            </button>
        @endif
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="card-title m-0 fw-bold"><i class="bx bx-list-ul text-primary me-2"></i>Accounts Directory</h5>
        <small class="text-muted">Overview of liquid assets, accounts, current funds, and transaction ledgers</small>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Account Name & Channel</th>
                    <th>Account / IBAN #</th>
                    <th>Opening Balance</th>
                    <th>Current Balance</th>
                    <th>Ledger Records</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($accounts as $acc)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary fs-4">
                                        <i class="bx {{ str_contains(strtolower($acc->name), 'cash') ? 'bx-money' : 'bx-building-house' }}"></i>
                                    </span>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fs-6">{{ $acc->name }}</strong>
                                    <small class="text-muted">Base Currency: {{ $currency }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary font-monospace fs-6 px-2 py-1">
                                {{ $acc->account_number ?: 'Vault / Petty Cash' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted fw-semibold">${{ number_format($acc->opening_balance, 2) }}</span>
                        </td>
                        <td>
                            <span class="fw-bold fs-5 text-success">${{ number_format($acc->current_balance, 2) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-info font-monospace">
                                {{ $acc->transactions_count ?? $acc->transactions()->count() }} Entries
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('accounts.show', $acc->id) }}" class="btn btn-icon btn-sm btn-outline-info" title="View Account Ledger">
                                    <i class="bx bx-book-open"></i>
                                </a>

                                @if($canEdit)
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAccountModal{{ $acc->id }}" title="Edit Account">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Account">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Account Modal -->
                    @if($canEdit)
                        <div class="modal fade" id="editAccountModal{{ $acc->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('accounts.update', $acc->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Account: {{ $acc->name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ $acc->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Account / IBAN Number</label>
                                                <input type="text" name="account_number" class="form-control" value="{{ $acc->account_number }}">
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-semibold">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-wallet fs-1 d-block mb-2 text-muted"></i>
                            No bank or cash accounts found. Click "Add Account" to configure your company accounts.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Account -->
@if($canCreate)
    <div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('accounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-plus-circle me-2"></i>Add Account</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Account Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Meezan Bank" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Account / IBAN Number</label>
                            <input type="text" name="account_number" class="form-control" placeholder="PK12MEZN0000123456789">
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<!-- Modal: Internal Transfer -->
@if($accounts->count() > 1)
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('accounts.transfer') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-transfer-alt me-2"></i>Internal Funds Transfer</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">From Source Account <span class="text-danger">*</span></label>
                            <select name="from_account_id" class="form-select" required>
                                <option value="">Select source account...</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Available: ${{ number_format($acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">To Destination Account <span class="text-danger">*</span></label>
                            <select name="to_account_id" class="form-select" required>
                                <option value="">Select destination account...</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} (Current: ${{ number_format($acc->current_balance, 2) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Transfer Amount ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Execute Transfer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
