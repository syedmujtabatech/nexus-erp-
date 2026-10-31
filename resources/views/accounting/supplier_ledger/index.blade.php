@extends('layouts.app')

@section('title', 'Supplier Statement & Ledger - Nexus ERP')

@section('content')
@php
    $currency = auth()->user()->currentCompany->currency ?? 'USD';
@endphp

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="card-title m-0 fw-bold"><i class="bx bx-file-find text-primary me-2"></i>Supplier Statement & Payable Ledger</h5>
        <small class="text-muted">Generate comprehensive vendor balance statements, purchases history, and payment audits</small>
    </div>

    <!-- Filter Form -->
    <div class="card-body bg-light border-top border-bottom py-3">
        <form action="{{ route('supplier_ledger.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Select Supplier / Vendor <span class="text-danger">*</span></label>
                <select name="vendor_id" class="form-select" required>
                    <option value="">-- Choose Vendor --</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>
                            {{ $v->name }} ({{ $v->phone ?: 'No Phone' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bx bx-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@if($selectedVendor)
    <!-- Vendor Statement Header & KPI Summary -->
    <div class="card border-0 shadow-sm mb-4 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center pb-3 border-bottom mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">{{ $selectedVendor->name }}</h4>
                <p class="text-muted small mb-0"><i class="bx bx-envelope me-1"></i>{{ $selectedVendor->email ?: 'N/A' }} | <i class="bx bx-phone me-1"></i>{{ $selectedVendor->phone ?: 'N/A' }}</p>
                @if($selectedVendor->address)
                    <small class="text-muted"><i class="bx bx-map me-1"></i>{{ $selectedVendor->address }}</small>
                @endif
            </div>
            <div class="d-flex gap-2 mt-2 mt-md-0">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Print Statement
                </button>
            </div>
        </div>

        <div class="row text-center text-md-start">
            <div class="col-md-4 mb-2 mb-md-0 border-end">
                <small class="text-muted text-uppercase fw-semibold d-block">Total Purchases Billed</small>
                <h3 class="fw-bold text-dark mb-0">${{ number_format($totalBilled, 2) }}</h3>
                <small class="text-muted">{{ $purchases->count() }} Purchase Bills</small>
            </div>
            <div class="col-md-4 mb-2 mb-md-0 border-end">
                <small class="text-muted text-uppercase fw-semibold d-block">Total Payments Remitted</small>
                <h3 class="fw-bold text-success mb-0">${{ number_format($totalPaid, 2) }}</h3>
                <small class="text-muted">{{ $transactions->count() }} Payment Vouchers</small>
            </div>
            <div class="col-md-4">
                <small class="text-muted text-uppercase fw-semibold d-block">Current Outstanding Payable</small>
                <h3 class="fw-bold text-danger mb-0">${{ number_format($balanceDue, 2) }}</h3>
                <small class="text-muted">Balance Due to Supplier</small>
            </div>
        </div>
    </div>

    <!-- Statement Unified Ledger Table -->
    @php
        // Merge purchases & transactions into chronologically sorted statement
        $entries = collect();
        foreach($purchases as $p) {
            $entries->push([
                'date' => $p->date,
                'created_at' => $p->created_at,
                'type' => 'Purchase Bill',
                'ref' => $p->purchase_number,
                'desc' => 'Stock Inward Bill',
                'debit' => $p->total,
                'credit' => 0,
            ]);
        }
        foreach($transactions as $t) {
            $entries->push([
                'date' => $t->date,
                'created_at' => $t->created_at,
                'type' => 'Supplier Payment',
                'ref' => 'TX-' . $t->id,
                'desc' => $t->description ?: 'Payment via ' . ($t->account->name ?? 'Account'),
                'debit' => 0,
                'credit' => $t->amount,
            ]);
        }
        $sortedEntries = $entries->sortBy('date');
        $runningBalance = 0;
    @endphp

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-list-check text-primary me-2"></i>Itemized Statement Ledger</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference #</th>
                        <th>Description</th>
                        <th class="text-end">Billed (Debit)</th>
                        <th class="text-end">Paid (Credit)</th>
                        <th class="text-end">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($sortedEntries as $e)
                        @php
                            $runningBalance += ($e['debit'] - $e['credit']);
                        @endphp
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($e['date'])->format('M d, Y') }}</td>
                            <td>
                                @if($e['type'] === 'Purchase Bill')
                                    <span class="badge bg-label-primary px-2 py-1"><i class="bx bx-cart me-1"></i> Bill</span>
                                @else
                                    <span class="badge bg-label-success px-2 py-1"><i class="bx bx-check-shield me-1"></i> Payment</span>
                                @endif
                            </td>
                            <td><strong class="font-monospace">{{ $e['ref'] }}</strong></td>
                            <td>{{ $e['desc'] }}</td>
                            <td class="text-end">
                                @if($e['debit'] > 0)
                                    <strong class="text-dark">${{ number_format($e['debit'], 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($e['credit'] > 0)
                                    <strong class="text-success">${{ number_format($e['credit'], 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="{{ $runningBalance > 0 ? 'text-danger' : 'text-dark' }}">
                                    ${{ number_format($runningBalance, 2) }}
                                </strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No transactions found for this vendor in the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm text-center py-5">
        <div class="card-body">
            <i class="bx bx-user-voice fs-1 text-muted d-block mb-2"></i>
            <h5 class="fw-bold text-dark">Please select a supplier from the filter above</h5>
            <p class="text-muted small">Choose a vendor to view their complete payable statement, purchase bills, and payment records.</p>
        </div>
    </div>
@endif
@endsection
