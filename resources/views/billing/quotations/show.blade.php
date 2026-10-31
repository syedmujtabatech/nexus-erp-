@extends('layouts.app')

@section('title', 'Quotation: ' . $quotation->quotation_number . ' - CRM Pro')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <!-- Actions Toolbar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Quotations
            </a>
            <div class="d-flex gap-2">
                @if($quotation->status != 'converted')
                    <form action="{{ route('quotations.convert', $quotation->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bx bx-receipt me-1"></i> Convert to Invoice
                        </button>
                    </form>
                    <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                @endif
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i> Print Quote
                </button>
            </div>
        </div>

        <!-- Document Card -->
        <div class="card p-4 p-md-5 mb-4 shadow-sm" id="printableArea">
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <h3 class="fw-bold text-primary mb-1">{{ $company->name ?? 'CRM Pro Workspace' }}</h3>
                    <p class="text-muted small mb-0">{{ $company->address ?? 'Corporate Headquarters' }}</p>
                    <p class="text-muted small mb-0">Email: {{ $company->email ?? auth()->user()->email }} | Phone: {{ $company->phone ?? 'N/A' }}</p>
                </div>
                <div class="text-end">
                    <h2 class="fw-bold text-dark mb-1">QUOTATION</h2>
                    <h5 class="text-primary fw-bold mb-1">{{ $quotation->quotation_number }}</h5>
                    <span class="badge bg-label-info text-capitalize fs-6">{{ ucfirst($quotation->status) }}</span>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="text-muted small text-uppercase fw-bold mb-2">Quote Prepared For:</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $quotation->customer->name }}</h5>
                    <p class="text-muted mb-1">{{ $quotation->customer->company_name ?: 'Individual Client' }}</p>
                    <p class="text-muted mb-0">{{ $quotation->customer->email }} | {{ $quotation->customer->phone }}</p>
                    <p class="text-muted small mb-0">{{ $quotation->customer->address }}</p>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <p class="mb-1"><span class="text-muted">Quotation Date:</span> <strong>{{ \Carbon\Carbon::parse($quotation->date)->format('M d, Y') }}</strong></p>
                    <p class="mb-1"><span class="text-muted">Valid Until:</span> <strong>{{ $quotation->valid_until ? \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') : '30 Days from issue' }}</strong></p>
                    <p class="mb-0"><span class="text-muted">Currency:</span> <strong>{{ $company->currency ?? 'USD' }}</strong></p>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <strong>{{ $item->product->name ?? $item->description }}</strong>
                                    @if($item->product && $item->description != $item->product->name)
                                        <small class="text-muted d-block">{{ $item->description }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end fw-semibold">${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals & Notes -->
            <div class="row">
                <div class="col-md-7">
                    <h6 class="fw-bold text-muted small text-uppercase">Terms & Special Conditions</h6>
                    <p class="small text-muted">{{ $quotation->notes ?: 'This quotation is subject to our standard terms of trade. Prices are valid for 30 days.' }}</p>
                </div>
                <div class="col-md-5">
                    <div class="p-3 bg-light rounded">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">${{ number_format($quotation->subtotal, 2) }}</span>
                        </div>
                        @if($quotation->discount > 0)
                            <div class="d-flex justify-content-between py-1 text-danger">
                                <span>Discount:</span>
                                <span>-${{ number_format($quotation->discount, 2) }}</span>
                            </div>
                        @endif
                        @if($quotation->tax > 0)
                            <div class="d-flex justify-content-between py-1 text-muted">
                                <span>Tax:</span>
                                <span>+${{ number_format($quotation->tax, 2) }}</span>
                            </div>
                        @endif
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center py-1">
                            <strong class="fs-5 text-dark">Grand Total:</strong>
                            <strong class="fs-4 text-primary">${{ number_format($quotation->total, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
