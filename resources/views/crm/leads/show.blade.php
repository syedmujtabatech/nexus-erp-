@extends('layouts.app')

@section('title', 'Lead Details: ' . $lead->title . ' - CRM Pro')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title m-0 fw-bold">{{ $lead->title }}</h5>
                    <small class="text-muted">Created {{ $lead->created_at->format('M d, Y') }}</small>
                </div>
                <div>
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
                    <span class="badge {{ $statusBadge }} text-capitalize fs-6">
                        {{ str_replace('_', ' ', $lead->status) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase fw-semibold">Estimated Deal Value</label>
                        <h3 class="text-success fw-bold">${{ number_format($lead->value, 2) }}</h3>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase fw-semibold">Associated Customer Profile</label>
                        <div>
                            @if($lead->customer)
                                <a href="{{ route('customers.show', $lead->customer->id) }}" class="fw-bold text-primary">
                                    <i class="bx bx-user-check me-1"></i> {{ $lead->customer->name }}
                                </a>
                            @else
                                <span class="text-muted">Not converted to customer yet.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold border-bottom pb-2">Opportunity Notes & Requirements</h6>
                    <div class="p-3 bg-light rounded text-dark">
                        {{ $lead->notes ?: 'No specific notes recorded for this lead.' }}
                    </div>
                </div>

                <div class="d-flex gap-2">
                    @if($lead->status != 'converted')
                        <form action="{{ route('leads.convert', $lead->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success fw-semibold">
                                <i class="bx bx-user-check me-1"></i> Convert to Customer & Quotation
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Leads
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title m-0 fw-bold"><i class="bx bx-user me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase">Contact Person</label>
                    <div class="fw-bold fs-6">{{ $lead->contact_name }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase">Email</label>
                    <div><a href="mailto:{{ $lead->email }}" class="text-dark">{{ $lead->email ?: 'N/A' }}</a></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small text-uppercase">Phone</label>
                    <div>{{ $lead->phone ?: 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
