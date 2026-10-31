@extends('layouts.app')

@section('title', 'Customer Ledger Statement - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title m-0 fw-bold"><i class="bx bx-book-open text-primary me-2"></i>Customer Account Statement Ledger</h5>
        <small class="text-muted">Generate full financial debit/credit statements and outstanding balance audits for clients</small>
    </div>
    <div class="card-body">
        <form action="{{ route('ledger.index') }}" method="GET" class="row align-items-end g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Select Customer Profile</label>
                <select name="customer_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Choose Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ isset($selectedCustomer) && $selectedCustomer->id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->company_name ?: 'Individual' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bx bx-search me-1"></i> View Statement
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedCustomer)
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Total Invoiced Volume</span>
                    <h3 class="fw-bold text-dark mb-0">${{ number_format($totalBilled, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Total Payments Received</span>
                    <h3 class="fw-bold text-success mb-0">${{ number_format($totalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Outstanding Balance Due</span>
                    <h3 class="fw-bold {{ $balanceDue > 0 ? 'text-danger' : 'text-muted' }} mb-0">
                        ${{ number_format($balanceDue, 2) }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title m-0 fw-bold">Statement: {{ $selectedCustomer->name }}</h5>
                <small class="text-muted">{{ $selectedCustomer->company_name ?: 'Individual Account' }}</small>
            </div>
            <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print Statement
            </button>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr class="table-light">
                        <th>Date</th>
                        <th>Reference / Description</th>
                        <th>Type</th>
                        <th class="text-end">Debit (Invoiced)</th>
                        <th class="text-end">Credit (Paid)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($invoices as $inv)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($inv->date)->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td><span class="badge bg-label-primary">Invoice</span></td>
                            <td class="text-end fw-bold text-dark">${{ number_format($inv->total, 2) }}</td>
                            <td class="text-end text-success fw-bold">${{ number_format($inv->paid_amount, 2) }}</td>
                            <td>
                                @php
                                    $badge = match($inv->status) {
                                        'paid' => 'bg-label-success',
                                        'partially_paid' => 'bg-label-warning',
                                        default => 'bg-label-danger'
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No transactions found for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
