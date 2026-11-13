@extends('layouts.app')

@section('title', 'Super Admin Dashboard - CRM Pro')

@section('content')
<div class="row">
    <!-- Stat 1: Registered Companies -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-buildings fs-4"></i></span>
                    </div>
                    <a href="{{ route('admin.companies') }}" class="btn btn-sm btn-icon btn-outline-primary"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Tenant Companies</span>
                <h3 class="card-title mb-1 fw-bold">{{ $totalCompanies }}</h3>
                <small class="text-primary fw-semibold"><i class="bx bx-building"></i> Registered Businesses</small>
            </div>
        </div>
    </div>

    <!-- Stat 2: System Users -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-user-check fs-4"></i></span>
                    </div>
                    <a href="{{ route('admin.users') }}" class="btn btn-sm btn-icon btn-outline-success"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Platform Users</span>
                <h3 class="card-title mb-1 fw-bold">{{ $totalUsers }}</h3>
                <small class="text-success fw-semibold"><i class="bx bx-shield-check"></i> Owners & Staff</small>
            </div>
        </div>
    </div>

    <!-- Stat 3: Total System Invoices -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-receipt fs-4"></i></span>
                    </div>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-icon btn-outline-warning"><i class="bx bx-chevron-right"></i></a>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Global Invoices</span>
                <h3 class="card-title mb-1 fw-bold">{{ $totalSystemInvoices }}</h3>
                <small class="text-warning fw-semibold"><i class="bx bx-file"></i> Across All Tenants</small>
            </div>
        </div>
    </div>

    <!-- Stat 4: Platform Revenue -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-dollar-circle fs-4"></i></span>
                    </div>
                    <span class="badge bg-label-info">USD Equivalent</span>
                </div>
                <span class="fw-semibold d-block mb-1 text-muted">Total Billed Volume</span>
                <h3 class="card-title mb-1 fw-bold text-info">${{ number_format($totalRevenue, 2) }}</h3>
                <small class="text-info fw-semibold"><i class="bx bx-line-chart"></i> Platform Activity</small>
            </div>
        </div>
    </div>
</div>

<!-- Tenant Companies Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0 fw-bold"><i class="bx bx-buildings text-primary me-2"></i>All Platform Tenant Companies</h5>
        <a href="{{ route('admin.companies') }}" class="btn btn-sm btn-outline-primary">View Full Management</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Company Name</th>
                    <th>Owner</th>
                    <th>Email</th>
                    <th>Currency</th>
                    <th>Plan</th>
                    <th>Created At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($companies as $comp)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded bg-label-primary font-weight-bold">
                                        {{ strtoupper(substr($comp->name, 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <strong class="text-dark">{{ $comp->name }}</strong>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">ID #{{ $comp->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $comp->owner->name ?? 'System Admin' }}</td>
                        <td>{{ $comp->email ?? 'N/A' }}</td>
                        <td><span class="badge bg-label-info">{{ $comp->currency }}</span></td>
                        <td>
                            <span class="badge bg-label-primary text-uppercase">{{ $comp->plan ?? 'trial' }}</span>
                        </td>
                        <td>{{ $comp->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <form action="{{ route('company.switch') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="company_id" value="{{ $comp->id }}">
                                <button type="submit" class="btn btn-sm btn-primary py-1 px-3">
                                    <i class="bx bx-log-in-circle me-1"></i> Inspect Workspace
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
