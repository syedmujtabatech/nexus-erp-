@extends('layouts.app')

@section('title', 'Account Ledger: ' . $account->name . ' - CRM Pro')

@section('content')
<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="avatar avatar-xl bg-label-primary rounded p-2 mb-3">
                    <i class="bx bx-wallet fs-2 text-primary"></i>
                </div>
                <h4 class="card-title fw-bold mb-1">{{ $account->name }}</h4>
                <p class="text-muted small mb-3">Account Number: <strong>{{ $account->account_number ?: 'N/A' }}</strong></p>

                <div class="p-3 bg-light rounded mb-3">
                    <span class="text-muted small text-uppercase fw-semibold d-block">Current Real-Time Balance</span>
                    <h2 class="fw-bold mb-0 text-success">${{ number_format($account->current_balance, 2) }}</h2>
                </div>

                <div class="text-muted small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span>Opening Balance:</span>
                        <strong>${{ number_format($account->opening_balance, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span>Created Date:</span>
                        <span>{{ $account->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bx bx-arrow-back me-1"></i> Back to Accounts
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 fw-bold"><i class="bx bx-book-content text-primary me-2"></i>Account Transaction Ledger</h5>
                <span class="badge bg-label-info">{{ $transactions->total() }} Entries</span>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-light">
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}</td>
                                <td>
                                    @php
                                        $typeBadge = match($tx->type) {
                                            'income' => 'bg-label-success',
                                            'expense' => 'bg-label-danger',
                                            default => 'bg-label-info'
                                        };
                                    @endphp
                                    <span class="badge {{ $typeBadge }} text-capitalize">
                                        {{ str_replace('_', ' ', $tx->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="d-block text-dark">{{ $tx->description }}</span>
                                    @if($tx->invoice_id)
                                        <small class="text-primary">Linked to Invoice</small>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $tx->type == 'income' || ($tx->type == 'internal_transfer' && $tx->to_account_id == $account->id) ? 'text-success' : 'text-danger' }}">
                                    {{ $tx->type == 'income' || ($tx->type == 'internal_transfer' && $tx->to_account_id == $account->id) ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No transactions recorded for this account yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light py-2">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
