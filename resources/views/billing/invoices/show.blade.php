@extends('layouts.app')

@section('title', 'Invoice: ' . $invoice->invoice_number . ' - CRM Pro')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Actions Toolbar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Invoices
            </a>
            <div class="d-flex flex-wrap gap-2">
                @if($invoice->status != 'paid')
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="bx bx-wallet me-1"></i> Record Payment
                    </button>
                    <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                @endif

                <!-- WhatsApp Quick Send -->
                <a href="{{ $whatsAppUrl }}" target="_blank" class="btn btn-success btn-sm fw-semibold" title="Send Invoice via WhatsApp">
                    <i class="bx bxl-whatsapp me-1 fs-5 align-middle"></i> Send WhatsApp
                </a>

                <a href="{{ route('invoices.pdf', $invoice->id) }}" class="btn btn-outline-dark btn-sm">
                    <i class="bx bx-download me-1"></i> PDF
                </a>
                <a href="{{ route('invoices.email', $invoice->id) }}" class="btn btn-outline-info btn-sm">
                    <i class="bx bx-paper-plane me-1"></i> Email
                </a>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Print
                </button>
            </div>
        </div>

        <!-- Document Card -->
        <div class="card p-4 p-md-5 mb-4 shadow-sm" id="printableInvoice">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h3 class="fw-bold text-primary mb-1">{{ $company->name ?? 'CRM Pro Workspace' }}</h3>
                    <p class="text-muted small mb-0">{{ $company->address ?? 'Corporate Headquarters' }}</p>
                    <p class="text-muted small mb-0">Email: {{ $company->email ?? auth()->user()->email }} | Phone: {{ $company->phone ?? 'N/A' }}</p>
                </div>
                <div class="text-end">
                    <h2 class="fw-bold text-dark mb-1">INVOICE</h2>
                    <h5 class="text-primary fw-bold mb-1">{{ $invoice->invoice_number }}</h5>
                    @php
                        $badgeColor = match($invoice->status) {
                            'paid' => 'bg-label-success',
                            'partially_paid' => 'bg-label-warning',
                            default => 'bg-label-danger'
                        };
                    @endphp
                    <span class="badge {{ $badgeColor }} text-capitalize fs-6">
                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase fw-bold mb-2">Billed To:</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $invoice->customer->name }}</h5>
                    <p class="text-muted mb-1">{{ $invoice->customer->company_name ?: 'Individual Client' }}</p>
                    <p class="text-muted mb-0">
                        <i class="bx bx-envelope me-1"></i>{{ $invoice->customer->email ?: 'N/A' }} | 
                        <i class="bx bx-phone me-1"></i>{{ $invoice->customer->phone ?: 'N/A' }}
                    </p>
                    <p class="text-muted small mb-0">{{ $invoice->customer->address }}</p>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <p class="mb-1"><span class="text-muted">Invoice Date:</span> <strong>{{ \Carbon\Carbon::parse($invoice->date)->format('M d, Y') }}</strong></p>
                    <p class="mb-1"><span class="text-muted">Due Date:</span> <strong>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'Due on receipt' }}</strong></p>
                    <p class="mb-1"><span class="text-muted">Currency:</span> <strong>{{ $company->currency ?? 'USD' }}</strong></p>
                    @if($invoice->salesPerson)
                        <p class="mb-0"><span class="text-muted">Sales Rep:</span> <strong>{{ $invoice->salesPerson->name }}</strong></p>
                    @endif
                </div>
            </div>

            <!-- Items Table with Product-Wise Margin Analysis -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 35%;">Item Description</th>
                            <th class="text-center" style="width: 10%;">Qty</th>
                            <th class="text-end" style="width: 15%;">Unit Cost</th>
                            <th class="text-end" style="width: 15%;">Sale Price</th>
                            <th class="text-end" style="width: 20%;">Total Price & Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product->name ?? $item->description }}</strong>
                                    @if($item->product && $item->description != $item->product->name)
                                        <small class="text-muted d-block">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end text-muted">
                                    ${{ number_format($item->effective_cost_price, 2) }}
                                </td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end">
                                    <div class="fw-bold">${{ number_format($item->total_price, 2) }}</div>
                                    @if($item->unit_price > 0)
                                        @if($item->profit >= 0)
                                            <span class="badge bg-label-success" style="font-size: 0.72rem;">
                                                <i class="bx bx-trending-up me-1"></i>+{{ $item->margin_percentage }}% (+${{ number_format($item->profit, 2) }})
                                            </span>
                                        @else
                                            <span class="badge bg-label-danger" style="font-size: 0.72rem;">
                                                <i class="bx bx-trending-down me-1"></i>{{ $item->margin_percentage }}% (-${{ number_format(abs($item->profit), 2) }})
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & Notes -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6 class="fw-bold text-muted small text-uppercase">Payment Instructions & Terms</h6>
                    <p class="small text-muted">{{ $invoice->notes ?: 'Please make payment to our designated account before due date.' }}</p>

                    <!-- WhatsApp Share Quick Card -->
                    <div class="card bg-label-success border border-success p-3 mt-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-success mb-1"><i class="bx bxl-whatsapp fs-5 me-1 align-middle"></i>WhatsApp Billing</h6>
                                <p class="small text-muted mb-0">Customer: {{ $invoice->customer->phone ?: 'No phone registered' }}</p>
                            </div>
                            <a href="{{ $whatsAppUrl }}" target="_blank" class="btn btn-success btn-sm">
                                <i class="bx bx-send me-1"></i> Send Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">${{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        @if($invoice->discount > 0)
                            <div class="d-flex justify-content-between py-1 text-danger">
                                <span>Discount:</span>
                                <span>-${{ number_format($invoice->discount, 2) }}</span>
                            </div>
                        @endif
                        @if($invoice->tax > 0)
                            <div class="d-flex justify-content-between py-1 text-muted">
                                <span>Tax:</span>
                                <span>+${{ number_format($invoice->tax, 2) }}</span>
                            </div>
                        @endif
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <span class="fw-bold text-dark">Invoice Total:</span>
                            <strong class="fs-5 text-dark">${{ number_format($invoice->total, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-1 text-success">
                            <span>Amount Paid:</span>
                            <strong class="fs-6">${{ number_format($invoice->paid_amount, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <strong class="text-danger fs-5">Balance Due:</strong>
                            <strong class="text-danger fs-4">${{ number_format(max(0, $invoice->total - $invoice->paid_amount), 2) }}</strong>
                        </div>
                    </div>

                    <!-- Management Profit & Sales Commission Breakdown -->
                    <div class="card border border-primary mt-3 p-3 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <span class="fw-bold text-primary small"><i class="bx bx-bar-chart-alt-2 me-1"></i>Management Profit Breakdown</span>
                            <span class="badge bg-label-primary">Internal Audit</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Total Cost of Goods (COGS):</span>
                            <strong>${{ number_format($invoice->total_cost, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Gross Margin:</span>
                            <strong class="text-success">${{ number_format($invoice->total_profit, 2) }} ({{ $invoice->gross_margin_percentage }}%)</strong>
                        </div>
                        @if($invoice->commission_amount > 0 || $invoice->salesPerson)
                            <div class="d-flex justify-content-between py-1 small">
                                <span class="text-muted">Rep Commission ({{ $invoice->commission_rate }}%):</span>
                                <strong class="text-warning">-${{ number_format($invoice->commission_amount, 2) }}</strong>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between pt-1 border-top small">
                            <strong class="text-dark">Estimated Net Profit:</strong>
                            <strong class="text-primary fs-6">${{ number_format($invoice->net_profit, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Receipts Log -->
            @if($invoice->transactions->count() > 0)
                <div class="border-top pt-4">
                    <h6 class="fw-bold mb-3"><i class="bx bx-history me-1 text-primary"></i>Payment Receipts & Audit History</h6>
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr class="table-light">
                                <th>Receipt Date</th>
                                <th>Account Credited</th>
                                <th>Payment Reference</th>
                                <th class="text-end">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->transactions as $tx)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}</td>
                                    <td>{{ $tx->account->name ?? 'Default Account' }}</td>
                                    <td>{{ $tx->description }}</td>
                                    <td class="text-end text-success fw-bold">${{ number_format($tx->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('invoices.pay', $invoice->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-money me-2"></i>Record Payment Receipt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Total Invoice:</span>
                            <strong>${{ number_format($invoice->total, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Already Paid:</span>
                            <span class="text-success fw-bold">${{ number_format($invoice->paid_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-1">
                            <span>Remaining Balance:</span>
                            <strong class="text-danger fs-5">${{ number_format(max(0, $invoice->total - $invoice->paid_amount), 2) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deposit Into Bank/Cash Account</label>
                        <select name="account_id" class="form-select" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} (Balance: ${{ number_format($acc->current_balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Amount ($)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ max(0, $invoice->total - $invoice->paid_amount) }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
