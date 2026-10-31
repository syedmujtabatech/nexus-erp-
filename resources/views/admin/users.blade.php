@extends('layouts.app')

@section('title', 'Platform Users - Super Admin - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-user-check text-primary me-2"></i>Global Platform Users</h5>
            <small class="text-muted">Manage all platform accounts, super admins, company owners, and staff</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSystemUserModal">
            <i class="bx bx-user-plus me-1"></i> Add System User
        </button>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>ID</th>
                    <th>User Profile</th>
                    <th>Email Address</th>
                    <th>Phone</th>
                    <th>System Role</th>
                    <th>Security OTP</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($users as $u)
                    <tr>
                        <td><strong>#{{ $u->id }}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary font-weight-bold">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </span>
                                </div>
                                <strong class="text-dark">{{ $u->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?: '—' }}</td>
                        <td>
                            @php
                                $roleBadge = match($u->role_id) {
                                    1 => 'bg-label-danger',
                                    2 => 'bg-label-primary',
                                    default => 'bg-label-info'
                                };
                            @endphp
                            <span class="badge {{ $roleBadge }} fw-semibold">
                                {{ $u->role->name ?? 'User' }}
                            </span>
                        </td>
                        <td>
                            @if($u->is_otp_verified)
                                <span class="badge bg-label-success"><i class="bx bx-check-shield me-1"></i> Verified</span>
                            @else
                                <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i> Pending OTP</span>
                            @endif
                        </td>
                        <td>
                            @if($u->id != auth()->id())
                                <div class="d-flex align-items-center gap-1">
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}" title="Edit User">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete user {{ $u->name }}?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete User">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit User Modal -->
                                <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.users.update', $u->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-primary">
                                                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit System User: {{ $u->name }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                                        <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Phone</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $u->phone }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Platform Role <span class="text-danger">*</span></label>
                                                            <select name="role_id" class="form-select" required>
                                                                @foreach($roles as $r)
                                                                    <option value="{{ $r->id }}" {{ $u->role_id == $r->id ? 'selected' : '' }}>
                                                                        {{ $r->name }} (Role ID: {{ $r->id }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">New Password (Leave blank to keep existing)</label>
                                                        <input type="password" name="password" class="form-control" placeholder="••••••••">
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
                            @else
                                <span class="badge bg-label-secondary">Current Admin</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No users found in database.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light py-2">
        {{ $users->links() }}
    </div>
</div>

<!-- Modal: Create System User -->
<div class="modal fade" id="createSystemUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-user-plus me-2"></i>Create New Platform User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="user@domain.com">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+123456789">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Platform Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->name }} (Role: {{ $r->id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Initial Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Create System User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
