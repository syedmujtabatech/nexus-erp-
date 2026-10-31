@extends('layouts.app')

@section('title', 'Record Supplier Purchase Bill - Nexus ERP')

@section('content')
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-cart text-primary me-2"></i>Record Supplier Purchase Bill</h5>
            <small class="text-muted">Procure goods & raw materials from suppliers. Stock will be auto-incremented into inventory.</small>
        </div>
        <span class="badge bg-label-primary fs-6"><i class="bx bx-package me-1"></i> Stock Inward Enabled</span>
    </div>

    <div class="card-body p-4">
        <form action="{{ route('purchases.store') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Supplier / Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" class="form-select" required>
                        <option value="">-- Choose Supplier --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}">
                                {{ $v->name }} ({{ $v->phone ?: 'No Phone' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Purchase / Bill Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Payment Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">
                <h6 class="fw-bold m-0"><i class="bx bx-box me-1 text-primary"></i>Purchase Line Items (Goods Received)</h6>
                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" onclick="addPurchaseRow()">
                    <i class="bx bx-plus me-1"></i> Add Another Product
                </button>
            </div>

            <!-- Items Table Header -->
            <div class="row g-2 mb-2 text-muted small fw-semibold text-uppercase d-none d-md-flex px-1">
                <div class="col-md-4">Product / Inventory Item</div>
                <div class="col-md-3">Item Description</div>
                <div class="col-md-2">Qty Received (+ Stock)</div>
                <div class="col-md-2">Purchase Unit Price ($)</div>
                <div class="col-md-1 text-center">Remove</div>
            </div>

            <div id="itemsContainer">
                <div class="row g-2 mb-2 item-row align-items-center">
                    <div class="col-md-4">
                        <select name="items[0][product_id]" class="form-select product-select" onchange="fillProductDetails(this)">
                            <option value="">-- Select Product --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}" data-name="{{ $p->name }}">
                                    {{ $p->name }} (Current Stock: {{ $p->stock_quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="items[0][description]" class="form-control item-desc" required placeholder="Description / Batch #">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[0][quantity]" class="form-control item-qty" value="1" min="1" oninput="calculateTotals()" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" name="items[0][unit_price]" class="form-control item-price" placeholder="0.00" oninput="calculateTotals()" required>
                    </div>
                    <div class="col-md-1 text-center">
                        <button type="button" class="btn btn-icon btn-outline-danger" onclick="removeRow(this)">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier Notes & Terms</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Vendor invoice number, consignment details, delivery receipt notes..."></textarea>
                    </div>

                    <!-- Initial Payment Section -->
                    <div class="card border p-3 bg-light">
                        <h6 class="fw-bold mb-2"><i class="bx bx-wallet me-1 text-success"></i>Immediate Initial Payment (Optional)</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Pay From Account</label>
                                <select name="payment_account_id" class="form-select form-select-sm">
                                    <option value="">-- No Immediate Payment --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->name }} (${{ number_format($acc->current_balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Initial Paid Amount ($)</label>
                                <input type="number" step="0.01" name="initial_payment" class="form-control form-control-sm" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-light border-0 p-3">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Subtotal:</span>
                            <span class="fw-bold fs-5 text-dark">$<span id="subtotalDisplay">0.00</span></span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted fw-semibold">Discount ($)</label>
                                <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm" value="0" oninput="calculateTotals()">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted fw-semibold">Tax ($)</label>
                                <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm" value="0" oninput="calculateTotals()">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fs-5 fw-bold text-dark">Grand Total:</span>
                            <span class="fs-4 fw-bold text-primary">$<span id="grandTotalDisplay">0.00</span></span>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bx bx-check-double me-1"></i> Confirm & Save Purchase Bill
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let rowIndex = 1;

function addPurchaseRow() {
    const container = document.getElementById('itemsContainer');
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 item-row align-items-center';
    row.innerHTML = `
        <div class="col-md-4">
            <select name="items[${rowIndex}][product_id]" class="form-select product-select" onchange="fillProductDetails(this)">
                <option value="">-- Select Product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" data-price="{{ $p->purchase_price }}" data-name="{{ $p->name }}">
                        {{ $p->name }} (Current Stock: {{ $p->stock_quantity }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="items[${rowIndex}][description]" class="form-control item-desc" required placeholder="Description / Batch #">
        </div>
        <div class="col-md-2">
            <input type="number" name="items[${rowIndex}][quantity]" class="form-control item-qty" value="1" min="1" oninput="calculateTotals()" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="form-control item-price" placeholder="0.00" oninput="calculateTotals()" required>
        </div>
        <div class="col-md-1 text-center">
            <button type="button" class="btn btn-icon btn-outline-danger" onclick="removeRow(this)">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    rowIndex++;
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    } else {
        alert('At least one purchase item is required.');
    }
}

function fillProductDetails(select) {
    const row = select.closest('.item-row');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price') || 0;
    const name = selectedOption.getAttribute('data-name') || '';

    if (name) {
        row.querySelector('.item-desc').value = name;
        row.querySelector('.item-price').value = parseFloat(price).toFixed(2);
    }
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        subtotal += (qty * price);
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const tax = parseFloat(document.getElementById('taxInput').value) || 0;
    const grandTotal = Math.max(0, (subtotal - discount) + tax);

    document.getElementById('subtotalDisplay').innerText = subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('grandTotalDisplay').innerText = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
</script>
@endsection
