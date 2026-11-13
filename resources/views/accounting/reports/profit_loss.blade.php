@extends('layouts.app')

@section('title', 'Profit & Loss Statement - Nexus ERP')

@section('content')
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-line-chart text-primary me-2"></i>Profit &amp; Loss Financial Statement</h5>
            <small class="text-muted">Standard GAAP / IFRS Accounting Flow: Revenue → COGS → Gross Profit → OPEX → Operating Profit → Net Profit</small>
        </div>
        <a href="{{ route('reports.profit_loss.pdf') }}" class="btn btn-outline-dark btn-sm">
            <i class="bx bx-download me-1"></i> Download PDF Statement
        </a>
    </div>
</div>

<!-- 6-Stage GAAP Financial Performance Sequence -->
<div class="row g-3 mb-4">
    <!-- 1. Total Revenue -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">1. Total Revenue</span>
                <h4 class="fw-bold text-success mb-0" style="font-size: 1.25rem;">{{ number_format($totalRevenue, 2) }}</h4>
                <small class="text-success fw-semibold" style="font-size: 0.72rem;"><i class="bx bx-check-circle"></i> Realized Sales</small>
            </div>
        </div>
    </div>

    <!-- 2. COGS -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">2. COGS</span>
                <h4 class="fw-bold text-danger mb-0" style="font-size: 1.25rem;">{{ number_format($cogs, 2) }}</h4>
                <small class="text-danger fw-semibold" style="font-size: 0.72rem;"><i class="bx bx-package"></i> Direct Costs</small>
            </div>
        </div>
    </div>

    <!-- 3. Gross Profit -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">3. Gross Profit</span>
                <h4 class="fw-bold {{ $grossProfit >= 0 ? 'text-primary' : 'text-danger' }} mb-0" style="font-size: 1.25rem;">
                    {{ number_format($grossProfit, 2) }}
                </h4>
                <small class="text-primary fw-semibold" style="font-size: 0.72rem;"><i class="bx bx-trending-up"></i> Rev - COGS</small>
            </div>
        </div>
    </div>

    <!-- 4. Operating Expenses -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">4. Operating Exp</span>
                <h4 class="fw-bold text-danger mb-0" style="font-size: 1.25rem;">{{ number_format($operatingExpenses, 2) }}</h4>
                <small class="text-danger fw-semibold" style="font-size: 0.72rem;"><i class="bx bx-receipt"></i> OPEX Overhead</small>
            </div>
        </div>
    </div>

    <!-- 5. Operating Profit -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">5. Operating Profit</span>
                <h4 class="fw-bold {{ $operatingProfit >= 0 ? 'text-info' : 'text-danger' }} mb-0" style="font-size: 1.25rem;">
                    {{ number_format($operatingProfit, 2) }}
                </h4>
                <small class="text-info fw-semibold" style="font-size: 0.72rem;"><i class="bx bx-bar-chart-alt-2"></i> EBIT Margin</small>
            </div>
        </div>
    </div>

    <!-- 6. Net Profit -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100" style="background: {{ $netProfit >= 0 ? 'rgba(37,99,235,0.06)' : 'rgba(239,68,68,0.06)' }}; border: 1.5px solid {{ $netProfit >= 0 ? '#2563eb' : '#ef4444' }} !important;">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">6. Net Profit</span>
                <h4 class="fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} mb-0" style="font-size: 1.25rem;">
                    {{ number_format($netProfit, 2) }}
                </h4>
                <small class="{{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} fw-semibold" style="font-size: 0.72rem;">
                    <i class="bx {{ $netProfit >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i> Bottom-Line
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Statement Breakdown Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0 fw-bold"><i class="bx bx-file-blank text-primary me-2"></i>Income Statement Breakdown ({{ $currency }})</h5>
        <span class="badge bg-label-primary px-3 py-1">Currency: {{ $currency }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 70%;">Accounting Category / Sequence</th>
                    <th class="text-end" style="width: 30%;">Amount ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                <!-- Step 1 -->
                <tr>
                    <td>
                        <strong class="text-success"><i class="bx bx-plus-circle me-2"></i> 1. Total Revenue (Sales Income &amp; Invoiced Receipts)</strong>
                        <small class="text-muted d-block ms-4">Gross invoiced billings and realized client payments</small>
                    </td>
                    <td class="text-end fw-bold text-success fs-6">+{{ number_format($totalRevenue, 2) }}</td>
                </tr>

                <!-- Step 2 -->
                <tr>
                    <td>
                        <strong class="text-danger"><i class="bx bx-minus-circle me-2"></i> 2. Cost of Goods Sold (COGS - Inventory &amp; Purchase Costs)</strong>
                        <small class="text-muted d-block ms-4">Direct inventory procurement and material unit costs</small>
                    </td>
                    <td class="text-end fw-bold text-danger fs-6">-{{ number_format($cogs, 2) }}</td>
                </tr>

                <!-- Step 3 -->
                <tr class="table-light fw-bold" style="background: #f8fafc;">
                    <td>
                        <strong class="text-dark"><i class="bx bx-equals me-2 text-primary"></i> 3. Gross Profit (Total Revenue − COGS)</strong>
                        <small class="text-muted d-block ms-4">Trading margin prior to operating overhead</small>
                    </td>
                    <td class="text-end fs-5 fw-bold {{ $grossProfit >= 0 ? 'text-primary' : 'text-danger' }}">
                        {{ number_format($grossProfit, 2) }}
                    </td>
                </tr>

                <!-- Step 4 -->
                <tr>
                    <td>
                        <strong class="text-danger"><i class="bx bx-minus-circle me-2"></i> 4. Operating Expenses (OPEX - Rent, Salaries, Utilities, Overheads)</strong>
                        <small class="text-muted d-block ms-4">Operational costs incurred to run day-to-day business</small>
                    </td>
                    <td class="text-end fw-bold text-danger fs-6">-{{ number_format($operatingExpenses, 2) }}</td>
                </tr>

                <!-- Step 5 -->
                <tr class="table-light fw-bold" style="background: #f1f5f9;">
                    <td>
                        <strong class="text-dark"><i class="bx bx-equals me-2 text-info"></i> 5. Operating Profit / EBIT (Gross Profit − Operating Expenses)</strong>
                        <small class="text-muted d-block ms-4">Operating performance before taxes and financing</small>
                    </td>
                    <td class="text-end fs-5 fw-bold {{ $operatingProfit >= 0 ? 'text-info' : 'text-danger' }}">
                        {{ number_format($operatingProfit, 2) }}
                    </td>
                </tr>

                <!-- Step 6 -->
                <tr class="table-primary fw-bold fs-5" style="background: rgba(37,99,235,0.12);">
                    <td>
                        <strong class="text-dark"><i class="bx bx-trophy me-2 text-warning"></i> 6. Net Profit / (Loss) — Final Bottom-Line</strong>
                        <small class="text-muted d-block ms-4">Final retained earnings for the business entity</small>
                    </td>
                    <td class="text-end fs-4 fw-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($netProfit, 2) }} {{ $currency }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
