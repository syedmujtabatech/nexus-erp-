@extends('layouts.app')

@section('title', 'Customer Profile: ' . $customer->name . ' - CRM Pro')

@section('content')
<div class="row">
    <!-- Customer Profile Header Card -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="avatar avatar-xl bg-label-primary rounded-circle mx-auto mb-3" style="width: 70px; height: 70px;">
                    <span class="avatar-initial fs-2 fw-bold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                </div>
                <h4 class="card-title fw-bold mb-1">{{ $customer->name }}</h4>
                <p class="text-muted mb-3">{{ $customer->company_name ?: 'Individual Client' }}</p>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <a href="{{ route('quotations.create', ['customer_id' => $customer->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-file me-1"></i> New Quote
                    </a>
                    <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-receipt me-1"></i> New Invoice
                    </a>
                </div>

                <div class="text-start border-top pt-3">
                    <div class="mb-2">
                        <span class="text-muted small text-uppercase fw-semibold d-block">Email Address</span>
                        <strong>{{ $customer->email ?: 'N/A' }}</strong>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small text-uppercase fw-semibold d-block">Phone Contact</span>
                        <div class="d-flex align-items-center justify-content-between">
                            <strong>{{ $customer->phone ?: 'N/A' }}</strong>
                            @if($customer->phone)
                                <a href="https://wa.me/{{ \App\Services\WhatsAppService::formatPhoneNumber($customer->phone) }}" target="_blank" class="btn btn-xs btn-outline-success py-0 px-2" title="Chat on WhatsApp">
                                    <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small text-uppercase fw-semibold d-block">Billing Address</span>
                        <span>{{ $customer->address ?: 'No address specified.' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Balances & Metrics -->
    <div class="col-lg-8 mb-4">
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-2">
                <div class="card">
                    <div class="card-body p-3">
                        <span class="fw-semibold text-muted d-block mb-1 small">Total Invoiced</span>
                        <h5 class="card-title mb-0 fw-bold text-dark">${{ number_format($totalBilled, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card">
                    <div class="card-body p-3">
                        <span class="fw-semibold text-muted d-block mb-1 small">Total Paid</span>
                        <h5 class="card-title mb-0 fw-bold text-success">${{ number_format($totalPaid, 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card">
                    <div class="card-body p-3">
                        <span class="fw-semibold text-muted d-block mb-1 small">Balance Due</span>
                        <h5 class="card-title mb-0 fw-bold {{ $balanceDue > 0 ? 'text-danger' : 'text-muted' }}">
                            ${{ number_format($balanceDue, 2) }}
                        </h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-2">
                <div class="card">
                    <div class="card-body p-3">
                        <span class="fw-semibold text-muted d-block mb-1 small">Credit Limit</span>
                        <h5 class="card-title mb-0 fw-bold text-info">
                            @if($customer->credit_limit > 0)
                                ${{ number_format($customer->credit_limit, 2) }}
                            @else
                                <span class="fs-6 text-muted">No Limit</span>
                            @endif
                        </h5>
                        @if($customer->credit_limit > 0)
                            <small class="{{ $balanceDue > $customer->credit_limit ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 0.72rem;">
                                Avail: ${{ number_format(max(0, $customer->credit_limit - $balanceDue), 2) }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Container -->
        <div class="nav-align-top">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link active fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-invoices">
                        <i class="bx bx-receipt me-1"></i> Invoices ({{ $customer->invoices->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-quotations">
                        <i class="bx bx-file me-1"></i> Quotations ({{ $customer->quotations->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link fw-semibold" role="tab" data-bs-toggle="tab" data-bs-target="#navs-leads">
                        <i class="bx bx-target-lock me-1"></i> Leads ({{ $customer->leads->count() }})
                    </button>
                </li>
            </ul>

            <div class="tab-content bg-white shadow-sm p-0">
                <!-- Invoices Tab -->
                <div class="tab-pane fade show active" id="navs-invoices" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr class="table-light">
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->invoices as $inv)
                                    <tr>
                                        <td>
                                            <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                                {{ $inv->invoice_number }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($inv->date)->format('M d, Y') }}</td>
                                        <td class="fw-semibold">${{ number_format($inv->total, 2) }}</td>
                                        <td class="text-success fw-semibold">${{ number_format($inv->paid_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match($inv->status) {
                                                    'paid' => 'bg-label-success',
                                                    'partially_paid' => 'bg-label-warning',
                                                    default => 'bg-label-danger'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                            <a href="{{ route('invoices.pdf', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-secondary"><i class="bx bx-download"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No invoices found for this customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quotations Tab -->
                <div class="tab-pane fade" id="navs-quotations" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr class="table-light">
                                    <th>Quotation #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->quotations as $quote)
                                    <tr>
                                        <td>
                                            <a href="{{ route('quotations.show', $quote->id) }}" class="fw-bold text-primary">
                                                {{ $quote->quotation_number }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($quote->date)->format('M d, Y') }}</td>
                                        <td class="fw-semibold">${{ number_format($quote->total, 2) }}</td>
                                        <td>
                                            <span class="badge bg-label-info">{{ ucfirst($quote->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('quotations.show', $quote->id) }}" class="btn btn-icon btn-sm btn-outline-primary"><i class="bx bx-show"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No quotations recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Leads Tab -->
                <div class="tab-pane fade" id="navs-leads" role="tabpanel">
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr class="table-light">
                                    <th>Lead Title</th>
                                    <th>Value</th>
                                    <th>Stage</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customer->leads as $ld)
                                    <tr>
                                        <td>
                                            <a href="{{ route('leads.show', $ld->id) }}" class="fw-bold text-dark">
                                                {{ $ld->title }}
                                            </a>
                                        </td>
                                        <td class="fw-semibold text-success">${{ number_format($ld->value, 2) }}</td>
                                        <td><span class="badge bg-label-success">{{ ucfirst($ld->status) }}</span></td>
                                        <td>{{ $ld->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No leads converted for this customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
