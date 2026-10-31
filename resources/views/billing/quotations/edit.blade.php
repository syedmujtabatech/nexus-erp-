@extends('layouts.app')

@section('title', 'Edit Quotation: ' . $quotation->quotation_number . ' - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-edit text-primary me-2"></i>Edit Quotation: {{ $quotation->quotation_number }}</h5>
            <small class="text-muted">Modify line items, prices, discounts, and customer details</small>
        </div>
        <span class="badge bg-label-info fs-6">{{ ucfirst($quotation->status) }}</span>
    </div>

    <div class="card-body">
        <form action="{{ route('quotations.update', $quotation->id) }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-5 mb-3">
                    <label class="form-label fw-semibold">Customer / Client <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $quotation->customer_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->company_name ?: 'Individual' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Quotation Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ $quotation->date }}" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Valid Until</label>
                    <input type="date" name="valid_until" class="form-control" value="{{ $quotation->valid_until }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ $quotation->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ $quotation->status == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="accepted" {{ $quotation->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="declined" {{ $quotation->status == 'declined' ? 'selected' : '' }}>Declined</option>
                        <option value="converted" {{ $quotation->status == 'converted' ? 'selected' : '' }}>Converted</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">
                <h6 class="fw-bold m-0"><i class="bx bx-list-plus me-1 text-primary"></i>Quotation Items</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addQuotationRow()">
                    <i class="bx bx-plus me-1"></i> Add Another Item
                </button>
            </div>

            <div id="itemsContainer">
                @foreach($quotation->items as $idx => $item)
                    <div class="row g-2 mb-2 item-row align-items-center">
                        <div class="col-md-4">
                            <select name="items[{{ $idx }}][product_id]" class="form-select product-select" onchange="fillProductDetails(this)">
                                <option value="">-- Select or Custom Item --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ $item->product_id == $p->id ? 'selected' : '' }} data-price="{{ $p->sale_price }}" data-name="{{ $p->name }}">
                                        {{ $p->name }} (${{ number_format($p->sale_price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="items[{{ $idx }}][description]" class="form-control item-desc" value="{{ $item->description }}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[{{ $idx }}][quantity]" class="form-control item-qty" value="{{ $item->quantity }}" min="1" oninput="calculateTotals()" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="items[{{ $idx }}][unit_price]" class="form-control item-price" value="{{ $item->unit_price }}" oninput="calculateTotals()" required>
                        </div>
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-icon btn-outline-danger" onclick="removeRow(this)">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Terms & Notes</label>
                    <textarea name="notes" class="form-control" rows="4">{{ $quotation->notes }}</textarea>
                </div>
                <div class="col-md-6">
                    <div class="card bg-lighter border-0 p-3">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Subtotal:</span>
                            <span class="fw-bold fs-5 text-dark">$<span id="subtotalDisplay">{{ number_format($quotation->subtotal, 2) }}</span></span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted fw-semibold">Discount ($)</label>
                                <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm" value="{{ $quotation->discount }}" oninput="calculateTotals()">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted fw-semibold">Tax ($)</label>
                                <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm" value="{{ $quotation->tax }}" oninput="calculateTotals()">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fw-bold text-dark fs-5">Grand Total:</span>
                            <span class="fw-bold fs-4 text-primary">$<span id="grandTotalDisplay">{{ number_format($quotation->total, 2) }}</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary fw-semibold px-4">
                    <i class="bx bx-save me-1"></i> Update Quotation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = {{ $quotation->items->count() + 1 }};

function fillProductDetails(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    const row = selectElem.closest('.item-row');
    if (selectedOption.value) {
        row.querySelector('.item-desc').value = selectedOption.getAttribute('data-name');
        row.querySelector('.item-price').value = selectedOption.getAttribute('data-price');
        calculateTotals();
    }
}

function addQuotationRow() {
    const container = document.getElementById('itemsContainer');
    const firstRow = container.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelector('.product-select').selectedIndex = 0;
    newRow.querySelector('.product-select').name = `items[${itemIndex}][product_id]`;
    newRow.querySelector('.item-desc').value = '';
    newRow.querySelector('.item-desc').name = `items[${itemIndex}][description]`;
    newRow.querySelector('.item-qty').value = 1;
    newRow.querySelector('.item-qty').name = `items[${itemIndex}][quantity]`;
    newRow.querySelector('.item-price').value = '';
    newRow.querySelector('.item-price').name = `items[${itemIndex}][unit_price]`;

    container.appendChild(newRow);
    itemIndex++;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    }
}

function calculateTotals() {
    let subtotal = 0;
    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        subtotal += (qty * price);
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const tax = parseFloat(document.getElementById('taxInput').value) || 0;
    const grandTotal = Math.max(0, (subtotal - discount) + tax);

    document.getElementById('subtotalDisplay').innerText = subtotal.toFixed(2);
    document.getElementById('grandTotalDisplay').innerText = grandTotal.toFixed(2);
}

document.addEventListener('DOMContentLoaded', calculateTotals);
</script>
@endsection
