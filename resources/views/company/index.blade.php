@extends('layouts.app')

@section('title', 'My Companies & SaaS Workspaces - Nexus ERP')

@section('content')
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-buildings text-primary me-2"></i>My Business Companies & SaaS Workspaces</h5>
            <small class="text-muted">Manage multi-tenant business profiles, base currencies, and SaaS subscription tiers</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
            <i class="bx bx-plus me-1"></i> Add New Company
        </button>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Workspace / Company</th>
                    <th>Contact Info</th>
                    <th>Currency</th>
                    <th>SaaS Plan Tier</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($companies as $comp)
                    @php $plan = $comp->plan ?? 'trial'; @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-md me-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary fs-4 fw-bold">
                                        <i class="bx bx-buildings"></i>
                                    </span>
                                </div>
                                <div>
                                    <strong class="d-block text-dark fs-6">{{ $comp->name }}</strong>
                                    <small class="text-muted">Created: {{ $comp->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <div><i class="bx bx-envelope text-muted me-1"></i> {{ $comp->email ?: 'N/A' }}</div>
                                <div><i class="bx bx-phone text-muted me-1"></i> {{ $comp->phone ?: 'N/A' }}</div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-info font-monospace fs-6 px-2 py-1">{{ $comp->currency }}</span>
                        </td>
                        <td>
                            @if($plan === 'premium')
                                <span class="badge bg-label-primary px-2 py-1 fw-bold"><i class="bx bx-diamond me-1"></i> PREMIUM ENTERPRISE</span>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">Unlimited Users & Full ERP</small>
                            @elseif($plan === 'advance')
                                <span class="badge bg-label-success px-2 py-1 fw-bold"><i class="bx bx-crown me-1"></i> ADVANCE PRO</span>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">20 Users, 500 Invoices & ERP</small>
                            @elseif($plan === 'basic')
                                <span class="badge bg-label-info px-2 py-1 fw-bold"><i class="bx bx-shield-alt me-1"></i> BASIC PLAN (5 USERS)</span>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">5 Users, 50 Invoices & CRM</small>
                            @else
                                <span class="badge bg-label-warning px-2 py-1 fw-bold"><i class="bx bx-time me-1"></i> 14-DAY TRIAL</span>
                                <small class="d-block text-muted" style="font-size: 0.75rem;">Free Trial Period</small>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->current_company_id == $comp->id)
                                <span class="badge bg-success py-2 px-3 fw-semibold">
                                    <i class="bx bx-check-circle me-1"></i> Active Workspace
                                </span>
                            @else
                                <form action="{{ route('company.switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="company_id" value="{{ $comp->id }}">
                                    <button type="submit" class="btn btn-outline-primary btn-sm fw-semibold">
                                        <i class="bx bx-transfer-alt me-1"></i> Switch Workspace
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <!-- Change SaaS Plan Button -->
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#changePlanModal{{ $comp->id }}" title="Change SaaS Plan">
                                    <i class="bx bx-crown me-1"></i> Plan
                                </button>

                                <!-- Edit Details -->
                                <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCompanyModal{{ $comp->id }}" title="Edit Details">
                                    <i class="bx bx-edit-alt"></i>
                                </button>

                                @if($companies->count() > 1)
                                    <form action="{{ route('companies.destroy', $comp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $comp->name }} and all associated data?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Company">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Modal: Change SaaS Plan -->
                    <div class="modal fade" id="changePlanModal{{ $comp->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('companies.plan', $comp->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-crown me-2"></i>Select SaaS Plan: {{ $comp->name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <p class="text-muted small mb-3">Choose the subscription tier for this company workspace:</p>

                                        <div class="list-group gap-2">
                                            <label class="list-group-item d-flex align-items-center justify-content-between p-3 border rounded {{ $plan === 'trial' ? 'border-primary bg-light' : '' }}">
                                                <div class="d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="radio" name="plan" value="trial" {{ $plan === 'trial' ? 'checked' : '' }}>
                                                    <div>
                                                        <strong class="d-block text-dark">14-Day Free Trial</strong>
                                                        <small class="text-muted">3 Users, 20 Invoices. Free testing.</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-warning fw-bold">FREE</span>
                                            </label>

                                            <label class="list-group-item d-flex align-items-center justify-content-between p-3 border rounded {{ $plan === 'basic' ? 'border-primary bg-light' : '' }}">
                                                <div class="d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="radio" name="plan" value="basic" {{ $plan === 'basic' ? 'checked' : '' }}>
                                                    <div>
                                                        <strong class="d-block text-dark">Basic Plan</strong>
                                                        <small class="text-muted">5 Users, 50 Invoices & Full CRM.</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-info fw-bold">$29 / mo</span>
                                            </label>

                                            <label class="list-group-item d-flex align-items-center justify-content-between p-3 border rounded {{ $plan === 'advance' ? 'border-primary bg-light' : '' }}">
                                                <div class="d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="radio" name="plan" value="advance" {{ $plan === 'advance' ? 'checked' : '' }}>
                                                    <div>
                                                        <strong class="d-block text-dark">Advance Pro Plan</strong>
                                                        <small class="text-muted">20 Users, 500 Invoices & ERP Purchases.</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-success fw-bold">$79 / mo</span>
                                            </label>

                                            <label class="list-group-item d-flex align-items-center justify-content-between p-3 border rounded {{ $plan === 'premium' ? 'border-primary bg-light' : '' }}">
                                                <div class="d-flex align-items-center">
                                                    <input class="form-check-input me-3" type="radio" name="plan" value="premium" {{ $plan === 'premium' ? 'checked' : '' }}>
                                                    <div>
                                                        <strong class="d-block text-dark">Premium Enterprise Suite</strong>
                                                        <small class="text-muted">Unlimited Users, Unlimited ERP & 24/7 Support.</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-primary fw-bold">$149 / mo</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success fw-semibold">Save Subscription Plan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal: Edit Company -->
                    <div class="modal fade" id="editCompanyModal{{ $comp->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('companies.update', $comp->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Company: {{ $comp->name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="{{ $comp->name }}" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ $comp->email }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">Phone</label>
                                                <input type="text" name="phone" class="form-control" value="{{ $comp->phone }}">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Base Currency <span class="text-danger">*</span></label>
                                            <input type="text" name="currency" class="form-control" value="{{ $comp->currency }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Address / Office Location</label>
                                            <textarea name="address" class="form-control" rows="2">{{ $comp->address }}</textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light py-2">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary fw-semibold">Update Company</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bx bx-buildings fs-1 d-block mb-2 text-muted"></i>
                            No business workspaces found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Company -->
<div class="modal fade" id="createCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('companies.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white fw-bold"><i class="bx bx-plus-circle me-2"></i>Create New Business Company</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company / Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Acme Logistics Ltd" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Business Email</label>
                            <input type="email" name="email" class="form-control" placeholder="info@acme.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+123456789">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Base Currency <span class="text-danger">*</span></label>
                            <select name="currency" class="form-select" required>
                                <option value="USD">USD ($)</option>
                                <option value="PKR" selected>PKR (Rs)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="AED">AED (AED)</option>
                                <option value="SAR">SAR (SAR)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">SaaS Plan Tier</label>
                            <select name="plan" class="form-select">
                                <option value="trial" selected>14-Day Free Trial</option>
                                <option value="basic">Basic Plan (5 Users - $29/mo)</option>
                                <option value="advance">Advance Pro (20 Users - $79/mo)</option>
                                <option value="premium">Premium Enterprise (Unlimited - $149/mo)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Address / Office Location</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Suite 400, Financial District..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Create & Launch Workspace</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
