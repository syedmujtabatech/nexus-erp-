@extends('layouts.app')

@section('title', 'Team & Sub-Users - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-group text-primary me-2"></i>Company Team Members & Sub-Users</h5>
            <small class="text-muted">Control granular system access (View, Create, Edit, Delete) per business module</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteUserModal">
            <i class="bx bx-user-plus me-1"></i> Add Sub-User
        </button>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Member Profile</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Granted Permissions Matrix</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($users as $u)
                    @php
                        $userPerms = [];
                        if ($u->pivot && $u->pivot->permissions) {
                            $userPerms = is_array($u->pivot->permissions) ? $u->pivot->permissions : json_decode($u->pivot->permissions, true) ?? [];
                        }
                    @endphp
                    <tr>
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
                            <span class="badge {{ $u->role_id == 2 ? 'bg-label-primary' : 'bg-label-info' }}">
                                {{ $u->role->name ?? 'Sub-User' }}
                            </span>
                        </td>
                        <td>
                            @if($u->role_id == 2 || $u->isSuperAdmin())
                                <span class="badge bg-label-success"><i class="bx bx-check-shield me-1"></i> FULL WORKSPACE OWNER ACCESS</span>
                            @else
                                <div style="max-width: 380px; white-space: normal;">
                                    @forelse($userPerms as $perm)
                                        <span class="badge bg-label-secondary mb-1 me-1 text-uppercase" style="font-size: 0.72rem;">
                                            {{ str_replace('_', ' ', $perm) }}
                                        </span>
                                    @empty
                                        <span class="text-muted small">No permissions granted</span>
                                    @endforelse
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($u->id != auth()->id())
                                <div class="d-flex align-items-center gap-1">
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}" title="Edit Permissions">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <form action="{{ route('team.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove {{ $u->name }} from this company workspace?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Remove User">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Edit Sub-User Modal -->
                                <div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form action="{{ route('team.update', $u->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-primary">
                                                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-shield-quarter me-2"></i>Edit Sub-User & Permissions: {{ $u->name }}</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Full Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Email Address</label>
                                                            <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Phone Number</label>
                                                            <input type="text" name="phone" class="form-control" value="{{ $u->phone }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">New Password (Leave blank to keep current)</label>
                                                            <input type="password" name="password" class="form-control" placeholder="••••••••">
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center mt-3 mb-2 border-bottom pb-2">
                                                        <h6 class="fw-bold m-0 text-primary"><i class="bx bx-lock-alt me-1"></i>Module Access Matrix</h6>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllPerms('editPermTable{{ $u->id }}', true)">Check All</button>
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllPerms('editPermTable{{ $u->id }}', false)">Uncheck All</button>
                                                        </div>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle" id="editPermTable{{ $u->id }}">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Module Name</th>
                                                                    <th class="text-center">View</th>
                                                                    <th class="text-center">Create</th>
                                                                    <th class="text-center">Edit</th>
                                                                    <th class="text-center">Delete</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $modules = [
                                                                        'leads' => 'Leads & Sales Pipeline',
                                                                        'customers' => 'Customers Directory',
                                                                        'products' => 'Products & Services',
                                                                        'quotations' => 'Quotations & Proposals',
                                                                        'invoices' => 'Invoices & Billing',
                                                                        'accounts' => 'Bank & Cash Accounts',
                                                                        'vendors' => 'Vendors & Suppliers',
                                                                        'expenses' => 'Operating Expenses',
                                                                    ];
                                                                @endphp

                                                                @foreach($modules as $modKey => $modLabel)
                                                                    <tr>
                                                                        <td><strong>{{ $modLabel }}</strong></td>
                                                                        <td class="text-center">
                                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_view" {{ in_array($modKey . '_view', $userPerms) ? 'checked' : '' }}>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_create" {{ in_array($modKey . '_create', $userPerms) ? 'checked' : '' }}>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_edit" {{ in_array($modKey . '_edit', $userPerms) ? 'checked' : '' }}>
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_delete" {{ in_array($modKey . '_delete', $userPerms) ? 'checked' : '' }}>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                                <tr>
                                                                    <td><strong>Customer Ledger & Reports</strong></td>
                                                                    <td class="text-center">
                                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="reports_view" {{ in_array('reports_view', $userPerms) ? 'checked' : '' }}>
                                                                    </td>
                                                                    <td class="text-center text-muted">—</td>
                                                                    <td class="text-center text-muted">—</td>
                                                                    <td class="text-center text-muted">—</td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Activity & Audit Logs</strong></td>
                                                                    <td class="text-center">
                                                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="activity_view" {{ in_array('activity_view', $userPerms) ? 'checked' : '' }}>
                                                                    </td>
                                                                    <td class="text-center text-muted">—</td>
                                                                    <td class="text-center text-muted">—</td>
                                                                    <td class="text-center text-muted">—</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold">Update Permissions</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="badge bg-label-secondary">Current User</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No team members added yet. Click "Add Sub-User" to invite colleagues with custom permissions!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Invite Sub-User -->
<div class="modal fade" id="inviteUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('team.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-user-plus me-2"></i>Create Sub-User & Assign Permissions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Michael Scott">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="michael@dundermifflin.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+123456789">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••••">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 mb-2 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-primary"><i class="bx bx-lock-alt me-1"></i>Assign Granular Module Permissions</h6>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleAllPerms('newPermTable', true)">Check All</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleAllPerms('newPermTable', false)">Uncheck All</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="newPermTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Module Name</th>
                                    <th class="text-center">View</th>
                                    <th class="text-center">Create</th>
                                    <th class="text-center">Edit</th>
                                    <th class="text-center">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $modules = [
                                        'leads' => 'Leads & Sales Pipeline',
                                        'customers' => 'Customers Directory',
                                        'products' => 'Products & Services',
                                        'quotations' => 'Quotations & Proposals',
                                        'invoices' => 'Invoices & Billing',
                                        'accounts' => 'Bank & Cash Accounts',
                                        'vendors' => 'Vendors & Suppliers',
                                        'expenses' => 'Operating Expenses',
                                    ];
                                @endphp

                                @foreach($modules as $modKey => $modLabel)
                                    <tr>
                                        <td><strong>{{ $modLabel }}</strong></td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_view" checked>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_create" checked>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_edit">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $modKey }}_delete">
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td><strong>Customer Ledger & Reports</strong></td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="reports_view" checked>
                                    </td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                </tr>
                                <tr>
                                    <td><strong>Activity & Audit Logs</strong></td>
                                    <td class="text-center">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="activity_view" checked>
                                    </td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                    <td class="text-center text-muted">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Save Sub-User & Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleAllPerms(tableId, state) {
    const table = document.getElementById(tableId);
    if (table) {
        table.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = state;
        });
    }
}
</script>
@endsection
