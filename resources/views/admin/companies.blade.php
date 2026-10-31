@extends('layouts.app')

@section('title', 'All Companies - Super Admin - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-buildings text-primary me-2"></i>Platform Tenant Companies</h5>
            <small class="text-muted">Global directory of all business tenants registered across the platform</small>
        </div>
        <span class="badge bg-label-primary">{{ $companies->total() }} Total Companies</span>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Primary Owner</th>
                    <th>Email Contact</th>
                    <th>Currency</th>
                    <th>Active Staff</th>
                    <th>Registered Date</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($companies as $comp)
                    <tr>
                        <td><span class="fw-bold">#{{ $comp->id }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                        {{ strtoupper(substr($comp->name, 0, 1)) }}
                                    </span>
                                </div>
                                <strong class="text-dark">{{ $comp->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $comp->owner->name ?? 'System Admin' }}</td>
                        <td>{{ $comp->email ?: '—' }}</td>
                        <td><span class="badge bg-label-info font-monospace">{{ $comp->currency }}</span></td>
                        <td>
                            <span class="badge bg-label-secondary"><i class="bx bx-user me-1"></i>{{ $comp->users->count() }} Members</span>
                        </td>
                        <td>{{ $comp->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No tenant companies registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light py-2">
        {{ $companies->links() }}
    </div>
</div>
@endsection
