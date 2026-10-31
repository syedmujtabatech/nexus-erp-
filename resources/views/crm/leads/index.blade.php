@extends('layouts.app')

@section('title', 'Leads Pipeline - CRM Pro')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('leads_create');
    $canEdit = auth()->user()->hasPermission('leads_edit');
    $canDelete = auth()->user()->hasPermission('leads_delete');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-target-lock text-primary me-2"></i>Sales Leads Pipeline</h5>
            <small class="text-muted">Track potential business opportunities and convert them to clients</small>
        </div>
        @if($canCreate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                <i class="bx bx-plus me-1"></i> Add New Lead
            </button>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Lead Title</th>
                    <th>Contact Person</th>
                    <th>Contact Info</th>
                    <th>Estimated Value</th>
                    <th>Pipeline Stage</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($leads as $lead)
                    <tr>
                        <td>
                            <a href="{{ route('leads.show', $lead->id) }}" class="fw-bold text-dark">
                                {{ $lead->title }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xs me-2">
                                    <span class="avatar-initial rounded-circle bg-label-secondary font-weight-bold">
                                        {{ strtoupper(substr($lead->contact_name, 0, 1)) }}
                                    </span>
                                </div>
                                <span>{{ $lead->contact_name }}</span>
                            </div>
                        </td>
                        <td>
                            <div><i class="bx bx-envelope me-1 text-muted"></i>{{ $lead->email ?? '—' }}</div>
                            <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $lead->phone ?? '—' }}</small>
                        </td>
                        <td>
                            <strong class="text-success">${{ number_format($lead->value, 2) }}</strong>
                        </td>
                        <td>
                            @php
                                $statusBadge = match($lead->status) {
                                    'converted' => 'bg-label-success',
                                    'won' => 'bg-label-success',
                                    'proposal_sent' => 'bg-label-warning',
                                    'qualified' => 'bg-label-info',
                                    'contacted' => 'bg-label-primary',
                                    'lost' => 'bg-label-danger',
                                    default => 'bg-label-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} text-capitalize">
                                {{ str_replace('_', ' ', $lead->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('leads.show', $lead->id) }}" class="btn btn-icon btn-sm btn-outline-info" title="View Lead Details">
                                    <i class="bx bx-show"></i>
                                </a>

                                @if($lead->status != 'converted' && $canCreate)
                                    <form action="{{ route('leads.convert', $lead->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="Convert to Customer & Quote">
                                            <i class="bx bx-user-check me-1"></i> Convert
                                        </button>
                                    </form>
                                @endif

                                @if($canEdit)
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editLeadModal{{ $lead->id }}" title="Edit Lead">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this lead?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Lead">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Lead Modal -->
                    @if($canEdit)
                        <div class="modal fade" id="editLeadModal{{ $lead->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('leads.update', $lead->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Lead</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Lead Title / Opportunity</label>
                                                <input type="text" name="title" class="form-control" value="{{ $lead->title }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Contact Person Name</label>
                                                <input type="text" name="contact_name" class="form-control" value="{{ $lead->contact_name }}" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $lead->email }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="{{ $lead->phone }}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Deal Value ($)</label>
                                                    <input type="number" step="0.01" name="value" class="form-control" value="{{ $lead->value }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Pipeline Stage</label>
                                                    <select name="status" class="form-select">
                                                        <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>New</option>
                                                        <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                        <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Qualified</option>
                                                        <option value="proposal_sent" {{ $lead->status == 'proposal_sent' ? 'selected' : '' }}>Proposal Sent</option>
                                                        <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Converted</option>
                                                        <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Lost</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notes & Context</label>
                                                <textarea name="notes" class="form-control" rows="3">{{ $lead->notes }}</textarea>
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
                        <td colspan="6" class="text-center py-4 text-muted">No sales leads found in this workspace. Click "Add New Lead" to get started!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Lead -->
@if($canCreate)
    <div class="modal fade" id="createLeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('leads.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-plus-circle me-2"></i>Create New Lead</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lead Title / Opportunity</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Website Redesign Project">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contact Person Name</label>
                            <input type="text" name="contact_name" class="form-control" required placeholder="e.g. Alex Morgan">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="alex@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Phone</label>
                                <input type="text" name="phone" class="form-control" placeholder="+123456789">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Estimated Value ($)</label>
                                <input type="number" step="0.01" name="value" class="form-control" required placeholder="3500.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Initial Stage</label>
                                <select name="status" class="form-select">
                                    <option value="new">New</option>
                                    <option value="contacted">Contacted</option>
                                    <option value="qualified">Qualified</option>
                                    <option value="proposal_sent">Proposal Sent</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Opportunity Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Details about requirements, budget, timeline..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Create Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
