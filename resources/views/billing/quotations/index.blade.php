@extends('layouts.app')

@section('title', 'Sales Quotations - CRM Pro')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('quotations_create');
    $canEdit = auth()->user()->hasPermission('quotations_edit');
    $canDelete = auth()->user()->hasPermission('quotations_delete');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-file text-primary me-2"></i>Sales Quotations</h5>
            <small class="text-muted">Generate formal price quotes and convert them to invoices upon agreement</small>
        </div>
        @if($canCreate)
            <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Create Quotation
            </a>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Quotation #</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Valid Until</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($quotations as $quote)
                    <tr>
                        <td>
                            <a href="{{ route('quotations.show', $quote->id) }}" class="fw-bold text-primary">
                                {{ $quote->quotation_number }}
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xs me-2">
                                    <span class="avatar-initial rounded-circle bg-label-info font-weight-bold">
                                        {{ strtoupper(substr($quote->customer->name ?? 'C', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="fw-semibold">{{ $quote->customer->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($quote->date)->format('M d, Y') }}</td>
                        <td>{{ $quote->valid_until ? \Carbon\Carbon::parse($quote->valid_until)->format('M d, Y') : '—' }}</td>
                        <td>
                            <strong class="text-dark fs-6">${{ number_format($quote->total, 2) }}</strong>
                        </td>
                        <td>
                            @php
                                $badgeColor = match($quote->status) {
                                    'converted' => 'bg-label-success',
                                    'accepted' => 'bg-label-success',
                                    'sent' => 'bg-label-info',
                                    'declined' => 'bg-label-danger',
                                    default => 'bg-label-warning'
                                };
                            @endphp
                            <span class="badge {{ $badgeColor }} text-capitalize">
                                {{ ucfirst($quote->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <a href="{{ route('quotations.show', $quote->id) }}" class="btn btn-icon btn-sm btn-outline-info" title="View Quotation">
                                    <i class="bx bx-show"></i>
                                </a>

                                @if($quote->status != 'converted' && $canCreate)
                                    <form action="{{ route('quotations.convert', $quote->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="Convert to Invoice">
                                            <i class="bx bx-receipt me-1"></i> Convert to Invoice
                                        </button>
                                    </form>
                                @endif

                                @if($canEdit && $quote->status != 'converted')
                                    <a href="{{ route('quotations.edit', $quote->id) }}" class="btn btn-icon btn-sm btn-outline-primary" title="Edit Quotation">
                                        <i class="bx bx-edit-alt"></i>
                                    </a>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('quotations.destroy', $quote->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete quote {{ $quote->quotation_number }}? Its sequence number will be recycled for the next quote.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Quote">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No quotations generated yet. Click "Create Quotation" to start.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
