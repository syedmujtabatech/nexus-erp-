@extends('layouts.app')

@section('title', 'CRM & ERP Command Dashboard - Nexus ERP')

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
    <div class="card bg-label-primary mb-4 border-0 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center p-4">
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

<!-- Top Header & CRM Quick Action Command Center -->
<div class="card border-0 shadow-sm mb-4 bg-white">
    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-md bg-label-primary rounded-3 d-flex align-items-center justify-content-center">
                <i class="bx bx-grid-alt fs-3 text-primary"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-dark">{{ $company->name ?? 'Enterprise Workspace' }}</h5>
                    <span class="badge bg-label-primary px-2 py-1 fw-semibold text-uppercase" style="font-size: 0.72rem;">{{ $currency }}</span>
                    <span class="badge bg-label-warning px-2 py-1 fw-semibold" style="font-size: 0.72rem;"><i class="bx bx-time me-1"></i>{{ strtoupper($company->plan ?? 'trial') }}</span>
                </div>
                <small class="text-muted"><i class="bx bx-calendar me-1"></i>{{ now()->format('l, d F Y') }} &bull; Enterprise CRM &amp; Financial Accounting</small>
            </div>
        </div>

        <!-- Quick CRM Action Pills -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                <i class="bx bx-target-lock me-1"></i> + New Lead
            </a>
            <a href="{{ route('quotations.create') }}" class="btn btn-sm btn-outline-info fw-semibold">
                <i class="bx bx-file me-1"></i> + New Quote
            </a>
            <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary fw-semibold shadow-sm">
                <i class="bx bx-receipt me-1"></i> + New Invoice
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary fw-semibold">
                <i class="bx bx-user-plus me-1"></i> + Add Customer
            </a>
        </div>
    </div>
</div>

<!-- 5 Responsive CRM & Financial Metric Scorecards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Pipeline Deal Value -->
    <div class="col-xl col-md-4 col-sm-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Pipeline Deals</span>
                    <div class="avatar avatar-xs bg-label-info rounded">
                        <i class="bx bx-target-lock text-info"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">{{ number_format($pipelineValue) }} <span class="fs-6 fw-normal text-muted">{{ $currency }}</span></h4>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-label-info py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bx bx-line-chart me-1"></i>{{ $totalLeads }} Total Leads
                    </span>
                    <a href="{{ route('leads.index') }}" class="text-muted small text-decoration-none"><i class="bx bx-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Realized Revenue -->
    <div class="col-xl col-md-4 col-sm-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Total Revenue</span>
                    <div class="avatar avatar-xs bg-label-success rounded">
                        <i class="bx bx-receipt text-success"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-success">{{ number_format($totalRevenue) }} <span class="fs-6 fw-normal text-muted">{{ $currency }}</span></h4>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-label-success py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bx bx-check-circle me-1"></i>{{ $totalInvoices }} Invoiced Deals
                    </span>
                    <a href="{{ route('invoices.index') }}" class="text-muted small text-decoration-none"><i class="bx bx-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Win Rate & Lead Conversion -->
    <div class="col-xl col-md-4 col-sm-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Win / Conversion</span>
                    <div class="avatar avatar-xs bg-label-warning rounded">
                        <i class="bx bx-trophy text-warning"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-warning">{{ $winRate }}%</h4>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-label-warning py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bx bx-medal me-1"></i>{{ $convertedLeads }} Won Clients
                    </span>
                    <span class="text-muted small" style="font-size: 0.72rem;">{{ $activeLeads }} Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Retained Customer Base -->
    <div class="col-xl col-md-4 col-sm-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Total Customers</span>
                    <div class="avatar avatar-xs bg-label-primary rounded">
                        <i class="bx bx-user-check text-primary"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 text-dark">{{ $totalCustomers }}</h4>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-label-primary py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bx bx-group me-1"></i>Retained Accounts
                    </span>
                    <a href="{{ route('customers.index') }}" class="text-muted small text-decoration-none"><i class="bx bx-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 5: Net Profit (GAAP) -->
    <div class="col-xl col-md-4 col-sm-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Net Bottom Profit</span>
                    <div class="avatar avatar-xs {{ $netProfit >= 0 ? 'bg-label-success' : 'bg-label-danger' }} rounded">
                        <i class="bx {{ $netProfit >= 0 ? 'bx-trending-up text-success' : 'bx-trending-down text-danger' }}"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-1 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($netProfit) }} <span class="fs-6 fw-normal text-muted">{{ $currency }}</span>
                </h4>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge {{ $netProfit >= 0 ? 'bg-label-success' : 'bg-label-danger' }} py-1 px-2 fw-semibold" style="font-size: 0.72rem;">
                        <i class="bx bx-calculator me-1"></i>Revenue − COGS − OPEX
                    </span>
                    <a href="{{ route('reports.profit_loss') }}" class="text-muted small text-decoration-none"><i class="bx bx-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Primary Analytics Section (ApexCharts) -->
<div class="row g-3 mb-4">
    <!-- Chart 1: Revenue, Expenses & Net Profit Trend Area Chart (8 Columns) -->
    <div class="col-lg-8 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bx bx-line-chart text-primary me-2"></i>Revenue, Cost &amp; Profit Trajectory
                    </h6>
                    <small class="text-muted">6-Month rolling performance across Realized Revenue, Operating Overhead, and Net Margin</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary"><i class="bx bx-stats me-1"></i>GAAP Flow</span>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- ApexChart Container -->
                <div id="revenueProfitTrendChart" style="min-height: 310px;"></div>

                <!-- 3-Way Financial Summary Strip -->
                <div class="row g-2 mt-2 pt-2 border-top text-center">
                    <div class="col-4">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Invoiced Revenue</small>
                        <strong class="text-success fs-6">{{ number_format($totalRevenue) }} {{ $currency }}</strong>
                    </div>
                    <div class="col-4 border-start border-end">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Operating Overhead</small>
                        <strong class="text-danger fs-6">{{ number_format($operatingExpenses) }} {{ $currency }}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Net Retained Margin</small>
                        <strong class="{{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} fs-6">{{ number_format($netProfit) }} {{ $currency }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Lead Conversion & Win Velocity Donut Chart (4 Columns) -->
    <div class="col-lg-4 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bx bx-pie-chart-alt-2 text-warning me-2"></i>Lead Conversion Ratio
                    </h6>
                    <small class="text-muted">Won vs Active vs Lost pipeline status</small>
                </div>
            </div>
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <!-- Apex Donut Chart Container -->
                <div id="leadConversionDonutChart" style="min-height: 250px;"></div>

                <!-- Conversion Breakdown Legend -->
                <div class="mt-3 pt-2 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted"><span class="badge badge-dot bg-success me-1"></span> Won / Converted:</span>
                        <strong class="text-success small">{{ $convertedLeads }} leads</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small text-muted"><span class="badge badge-dot bg-primary me-1"></span> Active in Pipeline:</span>
                        <strong class="text-primary small">{{ $activeLeads }} leads</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted"><span class="badge badge-dot bg-danger me-1"></span> Dropped / Lost:</span>
                        <strong class="text-danger small">{{ $lostLeads }} leads</strong>
                    </div>
                    <div class="alert alert-light py-2 px-3 mb-0 rounded-3 text-center border">
                        <span class="text-muted small">Sales Win Velocity: </span>
                        <strong class="text-dark">{{ $winRate }}% Success Rate</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Analytics: Sales Funnel & Workspace Overview / GAAP Performance -->
<div class="row g-3 mb-4">
    <!-- Chart 3: Sales Pipeline Velocity Funnel (Horizontal Bar Chart) (7 Columns) -->
    <div class="col-lg-7 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bx bx-filter-alt text-info me-2"></i>Sales Pipeline Velocity &amp; Funnel
                    </h6>
                    <small class="text-muted">Distribution of deals across the 6 standard CRM stages</small>
                </div>
                <a href="{{ route('leads.index') }}" class="btn btn-xs btn-outline-primary fw-semibold">
                    <i class="bx bx-folder-open me-1"></i> View Pipeline
                </a>
            </div>
            <div class="card-body p-3">
                <!-- Apex Funnel Bar Chart Container -->
                <div id="pipelineFunnelChart" style="min-height: 290px;"></div>

                <div class="d-flex flex-wrap justify-content-center gap-2 mt-2 pt-2 border-top">
                    <span class="badge bg-label-info"><i class="bx bx-circle me-1"></i>New ({{ $stageNew }})</span>
                    <span class="badge bg-label-primary"><i class="bx bx-phone me-1"></i>Contacted ({{ $stageContacted }})</span>
                    <span class="badge bg-label-secondary"><i class="bx bx-check-shield me-1"></i>Qualified ({{ $stageQualified }})</span>
                    <span class="badge bg-label-warning"><i class="bx bx-file me-1"></i>Proposal ({{ $stageProposal }})</span>
                    <span class="badge bg-label-success"><i class="bx bx-crown me-1"></i>Won ({{ $stageWon }})</span>
                    <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i>Lost ({{ $stageLost }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Workspace Overview & Financial Quota Progress (5 Columns) -->
    <div class="col-lg-5 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bx bx-buildings text-primary me-2"></i>Financial Performance (GAAP)
                    </h6>
                    <small class="text-muted">Standard 6-stage enterprise accounting breakdown</small>
                </div>
                <span class="badge bg-label-info">{{ $currency }}</span>
            </div>
            <div class="card-body p-3">
                <!-- Revenue Target Quota Progress Bar -->
                <div class="mb-3 p-3 bg-lighter rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Monthly Revenue Quota</span>
                        <span class="fw-bold text-dark small">{{ number_format($totalRevenue) }} / {{ number_format($monthlyTarget) }} {{ $currency }}</span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $quotaPercentage }}%;" aria-valuenow="{{ $quotaPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted mt-1 d-block text-end" style="font-size: 0.72rem;">{{ $quotaPercentage }}% of target realized</small>
                </div>

                <!-- 6 GAAP Sequence Steps -->
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="text-muted small">1. Total Revenue:</span>
                    <strong class="text-success">{{ number_format($totalRevenue) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="text-muted small">2. Less: COGS (Direct Costs):</span>
                    <strong class="text-danger">-{{ number_format($cogs) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom bg-light px-2 py-1 rounded">
                    <span class="fw-semibold small">3. Gross Profit:</span>
                    <strong class="{{ $grossProfit >= 0 ? 'text-primary' : 'text-danger' }}">{{ number_format($grossProfit) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom">
                    <span class="text-muted small">4. Operating Expenses (OPEX):</span>
                    <strong class="text-danger">-{{ number_format($operatingExpenses) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1 pb-1 border-bottom bg-light px-2 py-1 rounded">
                    <span class="fw-semibold small">5. Operating Profit (EBIT):</span>
                    <strong class="{{ $operatingProfit >= 0 ? 'text-info' : 'text-danger' }}">{{ number_format($operatingProfit) }} {{ $currency }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 px-2 py-1 rounded mt-1" style="background: rgba(37,99,235,0.08);">
                    <span class="fw-bold small text-dark">6. Net Profit (Final Margin):</span>
                    <strong class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }} fs-6">
                        {{ number_format($netProfit) }} {{ $currency }}
                    </strong>
                </div>

                <div class="mt-3 pt-2 border-top d-grid gap-2">
                    <a href="{{ route('reports.profit_loss') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                        <i class="bx bx-file-blank me-1"></i> Open Full Profit &amp; Loss Statement
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operational Section: Hot Sales Leads & Recent Invoices -->
<div class="row g-3">
    <!-- Active Hot Leads Widget (6 Columns) -->
    <div class="col-lg-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bx bx-target-lock text-primary me-2"></i>Hot Sales Leads &amp; Deals
                    </h6>
                    <small class="text-muted">High-priority leads currently progressing through pipeline</small>
                </div>
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="bx bx-plus me-1"></i> Add Lead
                </a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead Title &amp; Contact</th>
                            <th>Value ({{ $currency }})</th>
                            <th>Stage</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeads as $lead)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar avatar-xs bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bx bx-user text-info"></i>
                                        </div>
                                        <div>
                                            <strong class="text-dark d-block">{{ $lead->title }}</strong>
                                            <small class="text-muted">{{ $lead->contact_name }} &bull; {{ $lead->phone ?? 'No phone' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ number_format($lead->value, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $stageBadge = match($lead->status) {
                                            'new' => 'bg-label-info',
                                            'contacted' => 'bg-label-primary',
                                            'qualified' => 'bg-label-secondary',
                                            'proposal_sent' => 'bg-label-warning',
                                            'converted' => 'bg-label-success',
                                            'lost' => 'bg-label-danger',
                                            default => 'bg-label-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $stageBadge }} text-capitalize">{{ str_replace('_', ' ', $lead->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        @if($lead->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}" target="_blank" class="btn btn-icon btn-xs btn-outline-success" title="Chat on WhatsApp">
                                                <i class="bx bxl-whatsapp"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('leads.index') }}" class="btn btn-icon btn-xs btn-outline-primary" title="View Lead Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bx bx-target-lock fs-1 text-muted d-block mb-1"></i>
                                    No active leads in pipeline yet.
                                    <div class="mt-2">
                                        <a href="{{ route('leads.index') }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-plus me-1"></i> Create First Lead
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Commercial Invoices (6 Columns) -->
    <div class="col-lg-6 col-12">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <h6 class="card-title fw-bold mb-0 text-dark">
                        <i class="bx bx-receipt text-success me-2"></i>Recent Commercial Invoices
                    </h6>
                    <small class="text-muted">Latest billing records and client collections</small>
                </div>
                <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary fw-semibold shadow-sm">
                    <i class="bx bx-plus me-1"></i> New Invoice
                </a>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Total ({{ $currency }})</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $inv)
                            <tr>
                                <td>
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold text-primary">
                                        {{ $inv->invoice_number }}
                                    </a>
                                    <small class="d-block text-muted">{{ $inv->date ? \Carbon\Carbon::parse($inv->date)->format('d M Y') : 'N/A' }}</small>
                                </td>
                                <td>
                                    <strong class="text-dark d-block">{{ $inv->customer->name ?? 'N/A' }}</strong>
                                    <small class="text-muted">{{ $inv->customer->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ number_format($inv->total, 2) }}</span>
                                    <small class="d-block text-success">Paid: {{ number_format($inv->paid_amount, 2) }}</small>
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match($inv->status) {
                                            'paid' => 'bg-label-success',
                                            'partially_paid' => 'bg-label-warning',
                                            'unpaid' => 'bg-label-danger',
                                            default => 'bg-label-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeColor }} text-capitalize">{{ str_replace('_', ' ', $inv->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-icon btn-xs btn-outline-primary" title="View Invoice">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('invoices.pdf', $inv->id) }}" class="btn btn-icon btn-xs btn-outline-secondary" title="Download PDF">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bx bx-receipt fs-1 text-muted d-block mb-1"></i>
                                    No invoices generated yet.
                                    <div class="mt-2">
                                        <a href="{{ route('invoices.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-plus me-1"></i> Create First Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // Global Color Palette
    // -------------------------------------------------------------
    const primaryColor = '#696cff';
    const successColor = '#71dd37';
    const dangerColor = '#ff3e1d';
    const warningColor = '#ffab00';
    const infoColor = '#03c3ec';
    const secondaryColor = '#8592a3';
    const borderColor = '#f0f2f4';

    // -------------------------------------------------------------
    // 1. Revenue, Expense & Profit Trend Area Chart
    // -------------------------------------------------------------
    const trendEl = document.querySelector('#revenueProfitTrendChart');
    if (trendEl) {
        const trendOptions = {
            series: [
                {
                    name: 'Realized Revenue',
                    data: @json($monthlyRevenueSeries)
                },
                {
                    name: 'Operating Overhead',
                    data: @json($monthlyExpenseSeries)
                },
                {
                    name: 'Net Profit Margin',
                    data: @json($monthlyProfitSeries)
                }
            ],
            chart: {
                height: 310,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'inherit',
                zoom: { enabled: false }
            },
            colors: [successColor, dangerColor, primaryColor],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: [2.5, 2, 2.5]
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 95, 100]
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 5,
                padding: { top: 10, right: 15, left: 10, bottom: 0 }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                markers: { radius: 12 }
            },
            xaxis: {
                categories: @json($monthLabels),
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '12px', colors: '#64748b' }
                }
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return (val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val.toFixed(0));
                    },
                    style: { fontSize: '12px', colors: '#64748b' }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return Number(val).toLocaleString() + ' {{ $currency }}';
                    }
                }
            }
        };

        const trendChart = new ApexCharts(trendEl, trendOptions);
        trendChart.render();
    }

    // -------------------------------------------------------------
    // 2. Lead Conversion & Win Velocity Donut Chart
    // -------------------------------------------------------------
    const donutEl = document.querySelector('#leadConversionDonutChart');
    if (donutEl) {
        const wonCount = {{ (int) $convertedLeads }};
        const activeCount = {{ (int) $activeLeads }};
        const lostCount = {{ (int) $lostLeads }};
        const hasData = (wonCount + activeCount + lostCount) > 0;

        const donutOptions = {
            series: hasData ? [wonCount, activeCount, lostCount] : [1],
            labels: hasData ? ['Won / Converted', 'In Pipeline', 'Dropped / Lost'] : ['No Leads Yet'],
            colors: hasData ? [successColor, primaryColor, dangerColor] : [secondaryColor],
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'inherit'
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 4, colors: ['#ffffff'] },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '13px',
                                color: '#64748b'
                            },
                            value: {
                                show: true,
                                fontSize: '22px',
                                fontWeight: 700,
                                color: '#0f172a',
                                formatter: function () {
                                    return '{{ $winRate }}%';
                                }
                            },
                            total: {
                                show: true,
                                label: 'Win Rate',
                                fontSize: '12px',
                                color: '#64748b',
                                formatter: function () {
                                    return '{{ $winRate }}%';
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' deals';
                    }
                }
            }
        };

        const donutChart = new ApexCharts(donutEl, donutOptions);
        donutChart.render();
    }

    // -------------------------------------------------------------
    // 3. Sales Pipeline Funnel Horizontal Bar Chart
    // -------------------------------------------------------------
    const funnelEl = document.querySelector('#pipelineFunnelChart');
    if (funnelEl) {
        const funnelOptions = {
            series: [
                {
                    name: 'Leads Volume',
                    data: @json($funnelData)
                }
            ],
            chart: {
                type: 'bar',
                height: 290,
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    distributed: true,
                    borderRadius: 6,
                    barHeight: '55%',
                    dataLabels: { position: 'top' }
                }
            },
            colors: [infoColor, primaryColor, secondaryColor, warningColor, successColor, dangerColor],
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: { colors: ['#0f172a'], fontSize: '12px', fontWeight: 600 },
                formatter: function (val, opt) {
                    return val + ' leads';
                },
                offsetX: 6
            },
            legend: { show: false },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 5,
                padding: { top: 0, right: 35, left: 10, bottom: 0 }
            },
            xaxis: {
                categories: ['1. New Lead', '2. Contacted', '3. Qualified', '4. Proposal Sent', '5. Won (Converted)', '6. Lost'],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { fontSize: '12px', colors: '#64748b' }
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '12px', fontWeight: 500, colors: '#0f172a' }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' Active Leads';
                    }
                }
            }
        };

        const funnelChart = new ApexCharts(funnelEl, funnelOptions);
        funnelChart.render();
    }
});
</script>
@endpush
