@extends('layouts.app')

@section('title', 'Expenses Management & Reports - Nexus ERP')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('expenses_create');
    $canEdit = auth()->user()->hasPermission('expenses_edit');
    $canDelete = auth()->user()->hasPermission('expenses_delete');
    $currency = auth()->user()->currentCompany->currency ?? 'USD';
@endphp

<!-- Header & Add Button -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-credit-card text-primary me-2"></i>Operating Expenses Log & Financial Reports</h5>
            <small class="text-muted">Track operating overheads, supplier payments, utility bills, and filtered expense audits</small>
        </div>
        @if($canCreate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExpenseModal">
                <i class="bx bx-plus me-1"></i> Log Expense
            </button>
        @endif
    </div>

    <!-- Filter Toolbar -->
    <div class="card-body bg-light border-top border-bottom py-3">
        <form action="{{ route('expenses.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Vendor / Payee</label>
                <select name="vendor_id" class="form-select form-select-sm">
                    <option value="">All Vendors</option>
                    @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Payment Account</label>
                <select name="account_id" class="form-select form-select-sm">
                    <option value="">All Accounts</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="bx bx-filter-alt me-1"></i> Filter
                </button>
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
                    <i class="bx bx-reset"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Filtered Summary KPI Banner -->
    <div class="card-body bg-white py-3 border-bottom">
        <div class="row text-center text-md-start">
            <div class="col-md-4 mb-2 mb-md-0 border-end">
                <small class="text-muted text-uppercase fw-semibold d-block">Total Filtered Expenses</small>
                <h4 class="fw-bold text-danger mb-0">-${{ number_format($totalExpenseAmount, 2) }}</h4>
            </div>
            <div class="col-md-4 mb-2 mb-md-0 border-end">
                <small class="text-muted text-uppercase fw-semibold d-block">Transactions Count</small>
                <h4 class="fw-bold text-dark mb-0">{{ $expenseCount }} Records</h4>
            </div>
            <div class="col-md-4">
                <small class="text-muted text-uppercase fw-semibold d-block">Average per Expense</small>
                <h4 class="fw-bold text-primary mb-0">${{ number_format($averageExpense, 2) }}</h4>
            </div>
        </div>
    </div>

    <!-- Table of Expenses -->
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Category & Reference</th>
                    <th>Vendor / Payee</th>
                    <th>Payment Account</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($expenses as $exp)
                    <tr>
                        <td>
                            <strong class="text-dark d-block fs-6">{{ $exp->category }}</strong>
                            @if($exp->reference)
                                <small class="text-muted"><i class="bx bx-receipt me-1"></i>Ref: {{ $exp->reference }}</small>
                            @endif
                        </td>
                        <td>
                            @if($exp->vendor)
                                <span class="fw-semibold text-dark"><i class="bx bx-store-alt text-muted me-1"></i>{{ $exp->vendor->name }}</span>
                            @else
                                <span class="text-muted fst-italic">Direct Expense</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-secondary font-monospace">
                                <i class="bx bx-wallet me-1"></i>{{ $exp->account->name ?? 'Default Account' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted fw-semibold">{{ \Carbon\Carbon::parse($exp->date)->format('M d, Y') }}</span>
                        </td>
                        <td>
                            <strong class="text-danger fs-6">-${{ number_format($exp->amount, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                @if($canEdit)
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editExpenseModal{{ $exp->id }}" title="Edit Expense">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Deleting this expense will refund ${{ number_format($exp->amount, 2) }} back to {{ $exp->account->name ?? 'the account' }}. Proceed?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Expense & Refund Account">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Expense Modal -->
                    @if($canEdit)
                        <div class="modal fade" id="editExpenseModal{{ $exp->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <form action="{{ route('expenses.update', $exp->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Expense Record</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Expense Category <span class="text-danger">*</span></label>
                                                <input type="text" name="category" class="form-control" value="{{ $exp->category }}" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Vendor</label>
                                                    <select name="vendor_id" class="form-select">
                                                        <option value="">-- Direct Expense --</option>
                                                        @foreach($vendors as $v)
                                                            <option value="{{ $v->id }}" {{ $exp->vendor_id == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Date</label>
                                                    <input type="date" name="date" class="form-control" value="{{ $exp->date }}" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Receipt / Reference #</label>
                                                <input type="text" name="reference" class="form-control" value="{{ $exp->reference }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Description</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $exp->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-semibold">Update Expense</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-credit-card fs-1 d-block mb-2 text-muted"></i>
                            No operating expenses found matching your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- Modal: Create Expense -->
@if($canCreate)
    <div class="modal fade" id="createExpenseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('expenses.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-receipt me-2"></i>Log Business Expense</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 text-start">
                        @if($accounts->isEmpty())
                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bx bx-error-circle me-1"></i> No Bank/Cash accounts found. Please add an account in <strong>Bank & Cash Accounts</strong> first.
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Expense Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control" required placeholder="e.g. Office Rent, Cloud Hosting, Utility Bill, Packaging Material">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Vendor (Optional)</label>
                                <select name="vendor_id" class="form-select">
                                    <option value="">-- Direct Expense --</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Payment Source Account <span class="text-danger">*</span></label>
                                <select name="account_id" class="form-select" required>
                                    <option value="" disabled selected>-- Select Bank/Cash Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (Available: ${{ number_format($acc->current_balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Amount ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="amount" class="form-control" min="0.01" required placeholder="150.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Date Incurred <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Receipt / Reference #</label>
                            <input type="text" name="reference" class="form-control" placeholder="RCP-0091">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Details regarding this expenditure..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold" {{ $accounts->isEmpty() ? 'disabled' : '' }}>Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
