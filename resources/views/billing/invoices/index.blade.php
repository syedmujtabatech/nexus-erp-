@extends('layouts.app')

@section('title', 'Invoices Management - CRM Pro')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('invoices_create');
    $canEdit = auth()->user()->hasPermission('invoices_edit');
    $canDelete = auth()->user()->hasPermission('invoices_delete');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-receipt text-primary me-2"></i>Invoices Directory</h5>
            <small class="text-muted">Track customer invoices, payment collections, and gap-filling sequence numbering</small>
        </div>
        @if($canCreate)
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Create New Invoice
            </a>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Invoice #</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xs me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                        {{ strtoupper(substr($inv->customer->name ?? 'C', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="fw-semibold">{{ $inv->customer->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($inv->date)->format('M d, Y') }}</td>
                        <td>{{ $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('M d, Y') : '—' }}</td>
                        <td class="fw-bold text-dark">${{ number_format($inv->total, 2) }}</td>
                        <td class="text-success fw-bold">${{ number_format($inv->paid_amount, 2) }}</td>
                        <td>
                            @php
                                $badgeColor = match($inv->status) {
                                    'paid' => 'bg-label-success',
                                    'partially_paid' => 'bg-label-warning',
                                    default => 'bg-label-danger'
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }} text-capitalize">
                                {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-info" title="View Invoice">
                                    <i class="bx bx-show"></i>
                                </a>

                                <a href="{{ \App\Services\WhatsAppService::getInvoiceWhatsAppUrl($inv) }}" target="_blank" class="btn btn-icon btn-sm btn-outline-success" title="Send WhatsApp Invoice">
                                    <i class="bx bxl-whatsapp"></i>
                                </a>

                                <a href="{{ route('invoices.pdf', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-secondary" title="Download PDF">
                                    <i class="bx bx-download"></i>
                                </a>

                                @if($inv->status != 'paid')
                                    <button class="btn btn-icon btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $inv->id }}" title="Record Payment">
                                        <i class="bx bx-wallet"></i>
                                    </button>
                                @endif

                                @if($canEdit && $inv->status != 'paid')
                                    <a href="{{ route('invoices.edit', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit Invoice">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" onsubmit="return confirm('Deleting this invoice will recycle sequence number {{ $inv->invoice_number }} for your next created invoice. Proceed?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Invoice">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal: Record Payment -->
                    @if($inv->status != 'paid')
                        <div class="modal fade" id="payModal{{ $inv->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('invoices.pay', $inv->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-success">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-money me-2"></i>Record Payment: {{ $inv->invoice_number }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="alert alert-light border mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Total Invoice:</span>
                                                    <strong>${{ number_format($inv->total, 2) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Already Paid:</span>
                                                    <span class="text-success fw-bold">${{ number_format($inv->paid_amount, 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between border-top pt-1">
                                                    <span>Remaining Balance:</span>
                                                    <strong class="text-danger fs-5">${{ number_format($inv->total - $inv->paid_amount, 2) }}</strong>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Deposit Into Bank/Cash Account</label>
                                                <select name="account_id" class="form-select" required>
                                                    @foreach(\App\Models\Account::all() as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: ${{ number_format($acc->current_balance, 2) }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Payment Amount ($)</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ max(0, $inv->total - $inv->paid_amount) }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success fw-semibold">Record Payment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No invoices generated yet. Click "Create New Invoice" to start billing clients!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
