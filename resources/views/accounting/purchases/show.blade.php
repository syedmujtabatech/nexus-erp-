@extends('layouts.app')

@section('title', 'Purchase Bill ' . $purchase->purchase_number . ' - Nexus ERP')

@section('content')
@php
    $due = max(0, $purchase->total - $purchase->paid_amount);
    $currency = auth()->user()->currentCompany->currency ?? 'USD';
@endphp

<!-- Action Toolbar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Back to Purchases
    </a>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="bx bx-printer me-1"></i> Print Bill
        </button>
        @if($due > 0)
            <button class="btn btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#payBillModal">
                <i class="bx bx-dollar me-1"></i> Pay Supplier (${{ number_format($due, 2) }})
            </button>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm p-4">
    <!-- Invoice Header -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ asset('assets/img/logo.svg') }}" width="36" height="36" alt="Logo" />
                <h4 class="fw-bold mb-0 text-dark">NEXUS<span class="text-primary">ERP</span></h4>
            </div>
            <p class="text-muted small mb-0">{{ auth()->user()->currentCompany->name ?? 'Company' }}</p>
            <p class="text-muted small mb-0">{{ auth()->user()->currentCompany->email ?? '' }}</p>
        </div>
        <div class="text-end">
            <h3 class="fw-bold text-primary mb-1">PURCHASE BILL</h3>
            <span class="fw-bold font-monospace fs-5 text-dark d-block">#{{ $purchase->purchase_number }}</span>
            <div class="mt-2">
                @if($purchase->status === 'paid')
                    <span class="badge bg-success fs-6 px-3 py-1"><i class="bx bx-check me-1"></i> FULLY PAID</span>
                @elseif($purchase->status === 'partially_paid')
                    <span class="badge bg-warning fs-6 px-3 py-1"><i class="bx bx-time me-1"></i> PARTIALLY PAID</span>
                @else
                    <span class="badge bg-danger fs-6 px-3 py-1"><i class="bx bx-x me-1"></i> UNPAID</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Vendor and Dates Meta -->
    <div class="row mb-4">
        <div class="col-md-6">
            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Supplier / Vendor Details</small>
            <h5 class="fw-bold text-dark mb-1">{{ $purchase->vendor->name ?? 'N/A' }}</h5>
            <p class="text-muted small mb-1"><i class="bx bx-envelope me-1"></i>{{ $purchase->vendor->email ?: 'No email on file' }}</p>
            <p class="text-muted small mb-1"><i class="bx bx-phone me-1"></i>{{ $purchase->vendor->phone ?: 'No phone on file' }}</p>
            @if($purchase->vendor->address)
                <p class="text-muted small mb-0"><i class="bx bx-map me-1"></i>{{ $purchase->vendor->address }}</p>
            @endif
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <small class="text-muted text-uppercase fw-semibold d-block mb-1">Bill Information</small>
            <div class="small mb-1"><span class="text-muted">Bill Date:</span> <strong>{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</strong></div>
            <div class="small mb-1"><span class="text-muted">Due Date:</span> <strong>{{ $purchase->due_date ? \Carbon\Carbon::parse($purchase->due_date)->format('M d, Y') : 'Immediate' }}</strong></div>
            <div class="small mb-1"><span class="text-muted">Recorded By:</span> <strong>{{ $purchase->createdBy->name ?? 'System' }}</strong></div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive text-nowrap mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product / Inventory Item</th>
                    <th>Description</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            @if($item->product)
                                <strong class="text-dark d-block">{{ $item->product->name }}</strong>
                                <small class="text-muted"><i class="bx bx-package me-1"></i>In Warehouse: {{ $item->product->stock_quantity }} units</small>
                            @else
                                <strong class="text-dark">{{ $item->description }}</strong>
                            @endif
                        </td>
                        <td>{{ $item->description }}</td>
                        <td class="text-center font-monospace fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold text-dark">${{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals Calculation -->
    <div class="row justify-content-end mb-4">
        <div class="col-md-5">
            <div class="card bg-light border-0 p-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <strong class="text-dark">${{ number_format($purchase->subtotal, 2) }}</strong>
                </div>
                @if($purchase->discount > 0)
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Discount:</span>
                        <span>-${{ number_format($purchase->discount, 2) }}</span>
                    </div>
                @endif
                @if($purchase->tax > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax:</span>
                        <span>+${{ number_format($purchase->tax, 2) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between pt-2 border-top mb-2">
                    <span class="fs-5 fw-bold text-dark">Total Bill:</span>
                    <span class="fs-4 fw-bold text-primary">${{ number_format($purchase->total, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between text-success mb-1">
                    <span class="fw-semibold">Paid Amount:</span>
                    <span class="fw-bold">${{ number_format($purchase->paid_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between text-danger pt-1 border-top">
                    <span class="fw-bold">Balance Due:</span>
                    <span class="fw-bold fs-5">${{ number_format($due, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($purchase->notes)
        <div class="alert alert-light border small mb-4">
            <strong>Notes / Terms:</strong> {{ $purchase->notes }}
        </div>
    @endif

    <!-- Payment History -->
    <div class="border-top pt-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bx bx-history me-1 text-primary"></i>Payment Transactions History</h6>
        @if($purchase->transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Payment Account</th>
                            <th>Reference</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->transactions as $tx)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}</td>
                                <td><i class="bx bx-wallet text-muted me-1"></i>{{ $tx->account->name ?? 'Bank Account' }}</td>
                                <td>{{ $tx->description }}</td>
                                <td><strong class="text-success">${{ number_format($tx->amount, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small mb-0">No payment transactions recorded yet for this bill.</p>
        @endif
    </div>
</div>

<!-- Modal: Pay Bill -->
@if($due > 0)
    <div class="modal fade" id="payBillModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('purchases.pay', $purchase->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-dollar-circle me-2"></i>Record Supplier Payment</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-start">
                        <div class="alert alert-light border py-2 mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Supplier:</span>
                                <strong>{{ $purchase->vendor->name }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-danger fw-bold">
                                <span>Remaining Due:</span>
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
                                <label class="form-label fw-semibold">Amount to Pay ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control" max="{{ $due }}" value="{{ $due }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Reference Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Cheque #, Online Wire Ref, etc.">
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-semibold">Confirm Payment & Deduct Bank</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
