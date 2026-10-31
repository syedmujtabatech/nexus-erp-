@extends('layouts.app')

@section('page-title', 'Company Workspace Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="fa-solid fa-building text-primary me-2"></i>My Companies</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCompanyModal"><i class="fa-solid fa-plus me-1"></i> Create New Company</button>
</div>

<div class="row g-4 mb-4">
    @forelse($companies as $comp)
        <div class="col-md-4">
            <div class="card card-custom p-4 bg-white border-top border-primary border-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold m-0 text-dark">{{ $comp->name }}</h5>
                    <span class="badge bg-secondary">{{ $comp->currency }}</span>
                </div>
                <p class="text-muted small mb-1"><i class="fa-solid fa-envelope me-1"></i> {{ $comp->email ?? '-' }}</p>
                <p class="text-muted small mb-3"><i class="fa-solid fa-phone me-1"></i> {{ $comp->phone ?? '-' }}</p>

                @if(auth()->user()->current_company_id == $comp->id)
                    <span class="badge bg-success py-2 w-100"><i class="fa-solid fa-check-circle me-1"></i> Active Workspace</span>
                @else
                    <form action="{{ route('company.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="company_id" value="{{ $comp->id }}">
                        <button type="submit" class="btn btn-outline-primary btn-sm w-100 fw-bold">Switch to this Company</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">No companies created yet. Click <strong>Create New Company</strong> above to set up your business.</div>
        </div>
    @endforelse
</div>

<!-- Modal: Create Company -->
<div class="modal fade" id="createCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('companies.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-building-circle-check me-2"></i>Create New Company</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Acme Tech Solutions">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Company Email</label>
                            <input type="email" name="email" class="form-control" placeholder="info@acme.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Company Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+123456789">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Default Currency</label>
                        <select name="currency" class="form-select" required>
                            <option value="USD">USD ($)</option>
                            <option value="PKR">PKR (Rs)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="AED">AED (AED)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Company</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
