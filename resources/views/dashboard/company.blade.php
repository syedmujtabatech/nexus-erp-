@extends('layouts.app')

@section('title', 'Dashboard - CRM Pro')

@section('content')
@if($isSuperAdmin && $company)
    <div class="alert alert-primary d-flex justify-content-between align-items-center mb-4 py-2 px-3 border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(37,99,235,0.12) 0%, rgba(30,27,75,0.08) 100%); border-left: 4px solid #2563eb !important;">
        <div class="d-flex align-items-center gap-2">
            <i class="bx bx-shield-quarter text-primary fs-4"></i>
            <div>
                <strong class="text-dark">Super Admin Inspection Mode:</strong>
                <span class="text-muted ms-1">Viewing isolated workspace for <strong>{{ $company->name }}</strong> (ID #{{ $company->id }})</span>
            </div>
        </div>
        <a href="{{ route('company.switch.all') }}" class="btn btn-sm btn-outline-primary fw-semibold">
            <i class="bx bx-arrow-back me-1"></i> Return to Global Platform View
        </a>
    </div>
@endif

@if(!$company)
    <div class="card bg-label-primary mb-4 border-0">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title text-primary fw-bold mb-1"><i class="bx bx-buildings me-2"></i>Welcome! Set Up Your First Business Workspace</h5>
                <p class="card-text text-muted mb-0">Create your company profile whenever you are ready to start managing Leads, Quotations, Invoices, Accounts, and Team.</p>
            </div>
            <a href="{{ route('companies.index') }}" class="btn btn-primary fw-semibold">
                <i class="bx bx-plus me-1"></i> Create Company
            </a>
        </div>
    </div>
@endif

<!-- Metric Cards Row -->
<div class="row">
    <!-- Total Leads -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-target-lock fs-4"></i></span>
                    </div>
                    <a href="{{ route('leads.index') }}" class="btn btn-sm btn-icon btn-outline-info"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Sales Leads</span>
                <h3 class="card-title mb-1 fw-bold">{{ $totalLeads }}</h3>
                <small class="text-info fw-semibold"><i class="bx bx-trending-up"></i> In Active Pipeline</small>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-user-check fs-4"></i></span>
                    </div>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-icon btn-outline-success"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Total Customers</span>
                <h3 class="card-title mb-1 fw-bold">{{ $totalCustomers }}</h3>
                <small class="text-success fw-semibold"><i class="bx bx-check-double"></i> Retained Clients</small>
            </div>
        </div>
    </div>

    <!-- Total Invoices -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-receipt fs-4"></i></span>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-icon btn-outline-warning"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Invoices Issued</span>
                <h3 class="card-title mb-1 fw-bold">{{ $totalInvoices }}</h3>
                <small class="text-warning fw-semibold"><i class="bx bx-time-five"></i> Billed Records</small>
            </div>
        </div>
    </div>

    <!-- Net Profit -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between mb-2">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-wallet fs-4"></i></span>
                    </div>
                    <a href="{{ route('reports.profit_loss') }}" class="btn btn-sm btn-icon btn-outline-primary"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Net Profit ({{ $currency }})</span>
                <h4 class="card-title mb-1 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}" style="white-space: nowrap; font-size: 1.38rem; letter-spacing: -0.5px;">
                    {{ number_format($netProfit) }}
                </h4>
                <small class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                    <i class="bx {{ $netProfit >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i> Revenue - Cost
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Invoices Table -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 fw-bold"><i class="bx bx-receipt text-primary me-2"></i>Recent Invoices</h5>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm fw-semibold">
                    <i class="bx bx-plus me-1"></i> New Invoice
                </a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-light">
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Total ({{ $currency }})</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($recentInvoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                        {{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $inv->customer->name ?? 'N/A' }}</td>
                                <td class="fw-semibold">{{ number_format($inv->total, 2) }}</td>
                                <td class="text-success fw-semibold">{{ number_format($inv->paid_amount, 2) }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($inv->status) {
                                            'paid' => 'bg-label-success',
                                            'partially_paid' => 'bg-label-warning',
                                            'unpaid' => 'bg-label-danger',
                                            default => 'bg-label-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }}">{{ ucfirst(str_replace('_', ' ', $inv->status)) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-primary" title="View Invoice">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('invoices.pdf', $inv->id) }}" class="btn btn-icon btn-sm btn-outline-secondary" title="PDF">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No invoices generated yet. Create your first invoice!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Active Workspace Details -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0 fw-bold"><i class="bx bx-buildings text-info me-2"></i>Workspace Overview</h5>
                <span class="badge bg-label-info">{{ $company->currency ?? 'USD' }}</span>
            </div>
            <div class="card-body">
                <div class="p-3 bg-lighter rounded mb-3">
                    <h6 class="fw-bold mb-1">{{ $company->name ?? 'Default Workspace' }}</h6>
                    <small class="text-muted d-block"><i class="bx bx-envelope me-1"></i> {{ $company->email ?? auth()->user()->email }}</small>
                    <small class="text-muted d-block"><i class="bx bx-phone me-1"></i> {{ $company->phone ?? 'N/A' }}</small>
                </div>

                <h6 class="text-muted small text-uppercase fw-semibold mb-3">Financial Performance (GAAP)</h6>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="text-muted small">1. Total Revenue:</span>
                    <strong class="text-success">{{ number_format($totalRevenue) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="text-muted small">2. Less: COGS:</span>
                    <strong class="text-danger">-{{ number_format($cogs) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom bg-light px-2 py-1 rounded">
                    <span class="fw-semibold small">3. Gross Profit:</span>
                    <strong class="{{ $grossProfit >= 0 ? 'text-primary' : 'text-danger' }}">{{ number_format($grossProfit) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="text-muted small">4. Operating Exp:</span>
                    <strong class="text-danger">-{{ number_format($operatingExpenses) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom bg-light px-2 py-1 rounded">
                    <span class="fw-semibold small">5. Operating Profit:</span>
                    <strong class="{{ $operatingProfit >= 0 ? 'text-info' : 'text-danger' }}">{{ number_format($operatingProfit) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 px-2 py-1 rounded mt-1" style="background: rgba(37,99,235,0.08);">
                    <span class="fw-bold small">6. Net Profit:</span>
                    <strong class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} fs-6">
                        {{ number_format($netProfit) }} {{ $currency }}
                    </strong>
                </div>

                <div class="mt-4 pt-3 border-top d-grid gap-2">
                    <a href="{{ route('quotations.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-file me-1"></i> Create New Quotation
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-user-plus me-1"></i> Add Customer Contact
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
