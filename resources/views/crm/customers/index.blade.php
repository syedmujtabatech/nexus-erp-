@extends('layouts.app')

@section('title', 'Customers Directory - CRM Pro')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('customers_create');
    $canEdit = auth()->user()->hasPermission('customers_edit');
    $canDelete = auth()->user()->hasPermission('customers_delete');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-user-pin text-primary me-2"></i>Customers Directory</h5>
            <small class="text-muted">Manage your business clients, billing profiles, and customer history</small>
        </div>
        @if($canCreate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                <i class="bx bx-plus me-1"></i> Add New Customer
            </button>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Customer Name</th>
                    <th>Business / Company</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Credit Limit</th>
                    <th>History</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($customers as $cust)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                        {{ strtoupper(substr($cust->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <a href="{{ route('customers.show', $cust->id) }}" class="fw-bold text-dark d-block">
                                        {{ $cust->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold text-muted">{{ $cust->company_name ?: 'Individual' }}</span>
                        </td>
                        <td>{{ $cust->email ?: '—' }}</td>
                        <td>{{ $cust->phone ?: '—' }}</td>
                        <td>
                            @if($cust->credit_limit > 0)
                                <span class="badge bg-label-info fw-bold">${{ number_format($cust->credit_limit, 2) }}</span>
                                <small class="d-block text-muted">Due: ${{ number_format($cust->outstanding_balance, 2) }}</small>
                            @else
                                <span class="badge bg-label-secondary">No Limit</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-info me-1">{{ $cust->invoices_count ?? 0 }} Invoices</span>
                            <span class="badge bg-label-secondary">{{ $cust->leads_count ?? 0 }} Leads</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('customers.show', $cust->id) }}" class="btn btn-icon btn-sm btn-outline-info" title="View Customer Profile">
                                    <i class="bx bx-show"></i>
                                </a>

                                <a href="{{ route('quotations.create', ['customer_id' => $cust->id]) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Create Quotation">
                                    <i class="bx bx-file me-1"></i> Quote
                                </a>

                                @if($canEdit)
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $cust->id }}" title="Edit Customer">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('customers.destroy', $cust->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Customer">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Customer Modal -->
                    @if($canEdit)
                        <div class="modal fade" id="editCustomerModal{{ $cust->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('customers.update', $cust->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Customer</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Customer Full Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $cust->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Company / Organization</label>
                                                <input type="text" name="company_name" class="form-control" value="{{ $cust->company_name }}">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $cust->email }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="{{ $cust->phone }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Authorized Credit Limit ($)</label>
                                                <input type="number" step="0.01" min="0" name="credit_limit" class="form-control" value="{{ $cust->credit_limit ?? 0 }}" placeholder="0 for unlimited">
                                                <small class="text-muted">Maximum unpaid debt balance allowed before warning alerts.</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Billing Address</label>
                                                <textarea name="address" class="form-control" rows="2">{{ $cust->address }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-semibold">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No customers found. Click "Add New Customer" to register your first client!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Customer -->
@if($canCreate)
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-user-plus me-2"></i>Add New Customer</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Customer Full Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Michael Scott">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Company / Organization</label>
                            <input type="text" name="company_name" class="form-control" placeholder="e.g. Dunder Mifflin Inc.">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="client@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="+123456789">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Authorized Credit Limit ($)</label>
                            <input type="number" step="0.01" min="0" name="credit_limit" class="form-control" value="0" placeholder="0 for unlimited">
                            <small class="text-muted">Maximum unpaid debt balance allowed before warning alerts.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Billing Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Street, City, Postal Code..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
