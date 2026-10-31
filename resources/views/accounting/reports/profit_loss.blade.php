@extends('layouts.app')

@section('title', 'Profit & Loss Statement - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-line-chart text-primary me-2"></i>Profit & Loss Performance Report</h5>
            <small class="text-muted">Real-time accounting income statement calculation across sales, costs, and expenses</small>
        </div>
        <a href="{{ route('reports.profit_loss.pdf') }}" class="btn btn-outline-dark">
            <i class="bx bx-download me-1"></i> Download PDF Report
        </a>
    </div>
</div>

<!-- Metric Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Total Sales Income</span>
                <h3 class="fw-bold text-success mb-0">${{ number_format($totalSalesIncome, 2) }}</h3>
                <small class="text-success fw-semibold"><i class="bx bx-check-circle"></i> Realized Receipts</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Total Expenses & COGS</span>
                <h3 class="fw-bold text-danger mb-0">${{ number_format($cogs + $totalExpenses, 2) }}</h3>
                <small class="text-danger fw-semibold"><i class="bx bx-trending-down"></i> Outgoing Costs</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Net Profit / (Loss)</span>
                <h3 class="fw-bold {{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} mb-0">
                    ${{ number_format($netProfit, 2) }}
                </h3>
                <small class="{{ $netProfit >= 0 ? 'text-primary' : 'text-danger' }} fw-semibold">
                    <i class="bx {{ $netProfit >= 0 ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt' }}"></i> Bottom-Line Margin
                </small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title m-0 fw-bold">Statement Breakdown ({{ $currency }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Account Item / Description</th>
                    <th class="text-end">Amount ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong class="text-success"><i class="bx bx-plus-circle me-1"></i> Gross Sales Revenue (Realized Income)</strong>
                    </td>
                    <td class="text-end fw-bold text-success">${{ number_format($totalSalesIncome, 2) }}</td>
                </tr>
                <tr>
                    <td>
                        <strong class="text-danger"><i class="bx bx-minus-circle me-1"></i> Cost of Goods Sold (COGS - Product Purchase Cost)</strong>
                    </td>
                    <td class="text-end fw-bold text-danger">-${{ number_format($cogs, 2) }}</td>
                </tr>
                <tr class="table-light fw-bold">
                    <td>Gross Profit (Sales - COGS)</td>
                    <td class="text-end fs-6 text-dark">${{ number_format($grossProfit, 2) }}</td>
                </tr>
                <tr>
                    <td>
                        <strong class="text-danger"><i class="bx bx-minus-circle me-1"></i> Total Operating Overhead & Expenses</strong>
                    </td>
                    <td class="text-end fw-bold text-danger">-${{ number_format($totalExpenses, 2) }}</td>
                </tr>
                <tr class="table-primary fw-bold fs-5">
                    <td>Net Operating Profit / (Loss)</td>
                    <td class="text-end {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        ${{ number_format($netProfit, 2) }} {{ $currency }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
