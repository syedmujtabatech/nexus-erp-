@extends('layouts.app')

@section('title', 'Activity & Audit Logs - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-history text-primary me-2"></i>System Security & Audit Activity Logs</h5>
            <small class="text-muted">Real-time audit log tracking user logins, IP addresses, entity creations, updates, and deletes</small>
        </div>
        <span class="badge bg-label-info">{{ $logs->total() }} Log Entries</span>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Tenant Scope</th>
                    <th>Event Action</th>
                    <th>Description</th>
                    <th>IP Address & Network</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($logs as $log)
                    <tr>
                        <td>
                            <small class="text-muted fw-semibold">{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xs me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ $log->user->name ?? 'System' }}</strong>
                                    <small class="text-muted">{{ $log->user->email ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-secondary font-monospace">
                                {{ $log->company->name ?? 'Global' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badgeColor = match(true) {
                                    str_contains($log->action, 'login') => 'bg-label-success',
                                    str_contains($log->action, 'delete') => 'bg-label-danger',
                                    str_contains($log->action, 'create') => 'bg-label-primary',
                                    str_contains($log->action, 'update') => 'bg-label-warning',
                                    default => 'bg-label-info'
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }} text-uppercase" style="font-size: 0.72rem;">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-dark">{{ $log->description }}</span>
                        </td>
                        <td>
                            <code class="text-primary fw-bold">{{ $log->ip_address ?? '127.0.0.1' }}</code>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No activity events recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light py-2">
        {{ $logs->links() }}
    </div>
</div>
@endsection
