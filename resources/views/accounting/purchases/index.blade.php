@extends('layouts.app')

@section('title', 'Supplier Purchases & Bills - Nexus ERP')

@section('content')
@php
    $currency = auth()->user()->currentCompany->currency ?? 'USD';
@endphp

<!-- Header & Add Button -->
<div class="row mb-4">
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-lg bg-label-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bx bx-cart text-primary fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Purchases Billed</small>
                    <h4 class="fw-bold text-dark mb-0">${{ number_format($totalPurchases, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="avatar avatar-lg bg-label-success rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bx bx-check-circle text-success fs-3"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase fw-semibold">Total Paid to Suppliers</small>
                    <h4 class="fw-bold text-success mb-0">${{ number_format($totalPaid, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-danger rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bx bx-time text-danger fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-semibold">Accounts Payable (Due)</small>
                        <h4 class="fw-bold text-danger mb-0">${{ number_format($totalOutstanding, 2) }}</h4>
                    </div>
                </div>
                <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm fw-semibold">
                    <i class="bx bx-plus me-1"></i> New Purchase
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-receipt text-primary me-2"></i>Supplier Purchase Bills & Inventory Inward</h5>
            <small class="text-muted">Procure goods from vendors, increment warehouse stock, and manage payables</small>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> + Record Purchase Bill
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card-body bg-light border-top border-bottom py-2">
        <form action="{{ route('purchases.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="vendor_id" class="form-select form-select-sm">
                    <option value="">All Suppliers / Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}" placeholder="From">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}" placeholder="To">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bx bx-reset"></i></a>
            </div>
        </form>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bill #</th>
                    <th>Supplier / Vendor</th>
                    <th>Date</th>
                    <th>Total Billed</th>
                    <th>Paid</th>
                    <th>Balance Due</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($purchases as $p)
                    @php $due = max(0, $p->total - $p->paid_amount); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('purchases.show', $p->id) }}" class="fw-bold text-primary">
                                {{ $p->purchase_number }}
                            </a>
                        </td>
                        <td>
                            <strong class="text-dark d-block">{{ $p->vendor->name ?? 'Unknown Vendor' }}</strong>
                            <small class="text-muted">{{ $p->vendor->phone ?? '' }}</small>
                        </td>
                        <td>
                            <span class="text-muted fw-semibold">{{ \Carbon\Carbon::parse($p->date)->format('M d, Y') }}</span>
                            @if($p->due_date)
                                <small class="d-block text-muted" style="font-size: 0.72rem;">Due: {{ \Carbon\Carbon::parse($p->due_date)->format('M d') }}</small>
                            @endif
                        </td>
                        <td>
                            <strong class="text-dark fs-6">${{ number_format($p->total, 2) }}</strong>
                        </td>
                        <td>
                            <span class="text-success fw-semibold">${{ number_format($p->paid_amount, 2) }}</span>
                        </td>
                        <td>
                            @if($due > 0)
                                <strong class="text-danger">${{ number_format($due, 2) }}</strong>
                            @else
                                <span class="badge bg-label-success">Fully Settled</span>
                            @endif
                        </td>
                        <td>
                            @if($p->status === 'paid')
                                <span class="badge bg-label-success px-2 py-1 fw-semibold"><i class="bx bx-check me-1"></i> Paid</span>
                            @elseif($p->status === 'partially_paid')
                                <span class="badge bg-label-warning px-2 py-1 fw-semibold"><i class="bx bx-time me-1"></i> Partially Paid</span>
                            @else
                                <span class="badge bg-label-danger px-2 py-1 fw-semibold"><i class="bx bx-x me-1"></i> Unpaid</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('purchases.show', $p->id) }}" class="btn btn-icon btn-sm btn-outline-info" title="View Purchase Bill">
                                    <i class="bx bx-show"></i>
                                </a>

                                @if($due > 0)
                                    <button class="btn btn-icon btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#payModal{{ $p->id }}" title="Record Supplier Payment">
                                        <i class="bx bx-dollar"></i>
                                    </button>
                                @endif

                                <form action="{{ route('purchases.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Deleting this bill will reverse {{ $p->purchase_number }} and deduct its stock from inventory. Proceed?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Purchase Bill">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Payment Modal for Purchase -->
                    @if($due > 0)
                        <div class="modal fade" id="payModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('purchases.pay', $p->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-dollar-circle me-2"></i>Pay Supplier: {{ $p->vendor->name ?? 'Vendor' }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="alert alert-light border py-2 mb-3">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted">Bill Number:</span>
                                                    <strong>{{ $p->purchase_number }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted">Total Billed:</span>
                                                    <strong>${{ number_format($p->total, 2) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between text-danger fw-bold">
                                                    <span>Outstanding Due:</span>
                                                    <span>${{ number_format($due, 2) }}</span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Payment Bank / Cash Account <span class="text-danger">*</span></label>
                                                <select name="account_id" class="form-select" required>
                                                    <option value="" disabled selected>-- Select Account --</option>
                                                    @foreach($accounts as $acc)
                                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Available: ${{ number_format($acc->current_balance, 2) }})</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Payment Amount ($) <span class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" name="amount" class="form-control" max="{{ $due }}" value="{{ $due }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notes / Reference</label>
                                                <input type="text" name="notes" class="form-control" placeholder="Cheque #, Online transfer ID, etc.">
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success fw-semibold">Confirm & Deduct Funds</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bx bx-cart fs-1 d-block mb-2 text-muted"></i>
                            No purchase bills found. Click "+ Record Purchase Bill" to record stock received from suppliers.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $purchases->links() }}
        </div>
    @endif
</div>
@endsection
