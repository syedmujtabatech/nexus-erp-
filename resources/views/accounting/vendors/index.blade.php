@extends('layouts.app')

@section('title', 'Vendors & Suppliers - CRM Pro')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('vendors_create');
    $canEdit = auth()->user()->hasPermission('vendors_edit');
    $canDelete = auth()->user()->hasPermission('vendors_delete');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-store-alt text-primary me-2"></i>Vendors & Suppliers</h5>
            <small class="text-muted">Manage company suppliers, external contractors, and payees</small>
        </div>
        @if($canCreate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVendorModal">
                <i class="bx bx-plus me-1"></i> Register Vendor
            </button>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Vendor / Supplier Name</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Expenses Logged</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($vendors as $v)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-secondary font-weight-bold">
                                        {{ strtoupper(substr($v->name, 0, 1)) }}
                                    </span>
                                </div>
                                <strong class="text-dark">{{ $v->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $v->email ?: '—' }}</td>
                        <td>{{ $v->phone ?: '—' }}</td>
                        <td>{{ $v->address ?: '—' }}</td>
                        <td>
                            <span class="badge bg-label-info">{{ $v->expenses_count ?? 0 }} Records</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                @if($canEdit)
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editVendorModal{{ $v->id }}" title="Edit Vendor">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('vendors.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vendor?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Vendor">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Vendor Modal -->
                    @if($canEdit)
                        <div class="modal fade" id="editVendorModal{{ $v->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('vendors.update', $v->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Vendor</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Vendor Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $v->name }}" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $v->email }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="{{ $v->phone }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Address / Location</label>
                                                <textarea name="address" class="form-control" rows="2">{{ $v->address }}</textarea>
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
                        <td colspan="6" class="text-center py-4 text-muted">No vendors or suppliers found. Click "Register Vendor" to add one!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Vendor -->
@if($canCreate)
    <div class="modal fade" id="createVendorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('vendors.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-plus-circle me-2"></i>Register New Vendor</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Vendor / Supplier Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. AWS Cloud, Office Supplies Co.">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="billing@vendor.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="+123456789">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address / Location</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Street, City, Country..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Register Vendor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
