@extends('layouts.app')

@section('title', 'Edit Invoice: ' . $invoice->invoice_number . ' - CRM Pro')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-edit text-primary me-2"></i>Edit Invoice: {{ $invoice->invoice_number }}</h5>
            <small class="text-muted">Modify line items, cost prices, commission rates, and payment terms</small>
        </div>
        <span class="badge bg-label-info fs-6">{{ ucfirst(str_replace('_', ' ', $invoice->status)) }}</span>
    </div>

    <div class="card-body">
        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" id="editInvoiceForm">
            @csrf

            <!-- Client & Invoice Info -->
            <div class="row mb-3">
                <div class="col-md-5 mb-3">
                    <label class="form-label fw-semibold">Customer / Client <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customerSelect" class="form-select" required onchange="handleCustomerChange(this)">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" 
                                    data-credit-limit="{{ (float)$c->credit_limit }}" 
                                    data-balance="{{ (float)$c->outstanding_balance }}"
                                    {{ $invoice->customer_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->company_name ?: 'Individual' }})
                            </option>
                        @endforeach
                    </select>
                    <div id="creditLimitAlert" class="mt-2 p-2 rounded small" style="display: none;"></div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ $invoice->date }}" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Due Date</label>
                    <input type="date" name="due_date" class="form-control" value="{{ $invoice->due_date }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="unpaid" {{ $invoice->status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partially_paid" {{ $invoice->status == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- Sales Person & Commission Row -->
            <div class="row mb-4 bg-light p-3 rounded mx-0 border">
                <div class="col-md-6 mb-2 mb-md-0">
                    <label class="form-label fw-semibold small text-muted"><i class="bx bx-user me-1"></i>Assigned Sales Representative</label>
                    <select name="sales_person_id" class="form-select form-select-sm">
                        <option value="">-- Unassigned / Direct Company Sale --</option>
                        @foreach($teamUsers as $user)
                            <option value="{{ $user->id }}" {{ $invoice->sales_person_id == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label class="form-label fw-semibold small text-muted"><i class="bx bx-percentage me-1"></i>Sales Commission (%)</label>
                    <input type="number" step="0.1" min="0" max="100" name="commission_rate" id="commissionRate" class="form-control form-control-sm" value="{{ $invoice->commission_rate ?? 0 }}" placeholder="e.g. 5.0" oninput="calculateTotals()">
                </div>
                <div class="col-md-3 d-flex flex-column justify-content-center">
                    <small class="text-muted fw-semibold">Rep Commission Payout:</small>
                    <span class="fs-6 fw-bold text-dark" id="commissionAmountDisplay">${{ number_format($invoice->commission_amount ?? 0, 2) }}</span>
                </div>
            </div>

            <!-- Line Items Header & Add Button -->
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3 border-bottom pb-2">
                <div>
                    <h6 class="fw-bold m-0"><i class="bx bx-list-plus me-1 text-primary"></i>Invoice Line Items & Profit Analysis</h6>
                    <small class="text-muted">Real-time product profit & margin % per line item</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addInvoiceRow()">
                    <i class="bx bx-plus me-1"></i> Add Another Item
                </button>
            </div>

            <!-- Items Table Header -->
            <div class="row g-2 mb-2 text-muted small fw-semibold text-uppercase d-none d-lg-flex px-1">
                <div class="col-lg-3">Product / Service</div>
                <div class="col-lg-2">Description</div>
                <div class="col-lg-1">Qty</div>
                <div class="col-lg-2">Cost Price ($)</div>
                <div class="col-lg-2">Sale Price ($)</div>
                <div class="col-lg-1 text-end">Line Total</div>
                <div class="col-lg-1 text-center">Remove</div>
            </div>

            <!-- Items Container -->
            <div id="itemsContainer">
                @foreach($invoice->items as $idx => $item)
                    <div class="row g-2 mb-2 item-row align-items-center p-2 rounded border-bottom bg-white">
                        <div class="col-lg-3 col-12">
                            <select name="items[{{ $idx }}][product_id]" class="form-select product-select" onchange="fillProductDetails(this)">
                                <option value="">-- Select or Custom Item --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" 
                                            {{ $item->product_id == $p->id ? 'selected' : '' }}
                                            data-price="{{ $p->sale_price }}" 
                                            data-cost="{{ $p->purchase_price }}" 
                                            data-name="{{ $p->name }}">
                                        {{ $p->name }} (Sale: ${{ number_format($p->sale_price, 2) }} | Cost: ${{ number_format($p->purchase_price, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-12">
                            <input type="text" name="items[{{ $idx }}][description]" class="form-control item-desc" value="{{ $item->description }}" required placeholder="Item description">
                        </div>
                        <div class="col-lg-1 col-4">
                            <input type="number" name="items[{{ $idx }}][quantity]" class="form-control item-qty" value="{{ $item->quantity }}" min="1" oninput="calculateTotals()" required placeholder="Qty">
                        </div>
                        <div class="col-lg-2 col-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][cost_price]" class="form-control item-cost" value="{{ $item->cost_price ?: ($item->product->purchase_price ?? 0) }}" placeholder="Cost" oninput="calculateTotals()">
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Unit Cost</small>
                        </div>
                        <div class="col-lg-2 col-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" class="form-control item-price" value="{{ $item->unit_price }}" placeholder="Sale" oninput="calculateTotals()" required>
                            </div>
                            <small class="text-muted d-block" style="font-size: 0.72rem;">Unit Sale</small>
                        </div>
                        <div class="col-lg-1 col-6 text-lg-end">
                            <span class="fw-bold fs-6 text-dark item-line-total">${{ number_format($item->total_price, 2) }}</span>
                            <div class="item-profit-badge mt-1 small"></div>
                        </div>
                        <div class="col-lg-1 col-6 text-center">
                            <button type="button" class="btn btn-icon btn-outline-danger btn-sm" onclick="removeRow(this)">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Notes & Financial Summary -->
            <div class="row mt-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Payment Terms & Notes</label>
                    <textarea name="notes" class="form-control" rows="5">{{ $invoice->notes }}</textarea>
                </div>

                <div class="col-md-6">
                    <div class="card bg-lighter border-0 p-3 shadow-none">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted fw-semibold">Invoice Subtotal:</span>
                            <span class="fw-bold fs-5 text-dark">$<span id="subtotalDisplay">{{ number_format($invoice->subtotal, 2) }}</span></span>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-muted fw-semibold">Discount ($)</label>
                                <input type="number" step="0.01" min="0" name="discount" id="discountInput" class="form-control form-control-sm" value="{{ $invoice->discount }}" oninput="calculateTotals()">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted fw-semibold">Tax ($)</label>
                                <input type="number" step="0.01" min="0" name="tax" id="taxInput" class="form-control form-control-sm" value="{{ $invoice->tax }}" oninput="calculateTotals()">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom">
                            <span class="fw-bold text-dark fs-5">Grand Total:</span>
                            <span class="fw-bold fs-4 text-primary">$<span id="grandTotalDisplay">{{ number_format($invoice->total, 2) }}</span></span>
                        </div>

                        <!-- Profit & Margin Analysis Widget -->
                        <div class="mt-3 p-3 bg-white rounded border">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted small fw-semibold"><i class="bx bx-archive me-1"></i>Total Cost (COGS):</span>
                                <span class="fw-bold text-muted small">$<span id="totalCostDisplay">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark small"><i class="bx bx-trending-up text-success me-1"></i>Gross Profit:</span>
                                <div>
                                    <span class="fw-bold text-success fs-6">$<span id="grossProfitDisplay">0.00</span></span>
                                    <span class="badge bg-label-success ms-1" id="grossMarginBadge">0.0%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                                <span><i class="bx bx-user-check me-1"></i>Sales Commission:</span>
                                <span>-$<span id="commissionSummaryDisplay">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold text-primary small">Estimated Net Profit:</span>
                                <strong class="fs-6 text-primary">$<span id="netProfitDisplay">0.00</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary fw-semibold px-4">
                    <i class="bx bx-save me-1"></i> Update Invoice
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = {{ $invoice->items->count() + 1 }};

function fillProductDetails(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    const row = selectElem.closest('.item-row');
    if (selectedOption && selectedOption.value) {
        row.querySelector('.item-desc').value = selectedOption.getAttribute('data-name') || '';
        row.querySelector('.item-price').value = selectedOption.getAttribute('data-price') || '0';
        row.querySelector('.item-cost').value = selectedOption.getAttribute('data-cost') || '0';
        calculateTotals();
    }
}

function addInvoiceRow() {
    const container = document.getElementById('itemsContainer');
    const firstRow = container.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelector('.product-select').selectedIndex = 0;
    newRow.querySelector('.product-select').name = `items[${itemIndex}][product_id]`;
    newRow.querySelector('.item-desc').value = '';
    newRow.querySelector('.item-desc').name = `items[${itemIndex}][description]`;
    newRow.querySelector('.item-qty').value = 1;
    newRow.querySelector('.item-qty').name = `items[${itemIndex}][quantity]`;
    newRow.querySelector('.item-cost').value = '';
    newRow.querySelector('.item-cost').name = `items[${itemIndex}][cost_price]`;
    newRow.querySelector('.item-price').value = '';
    newRow.querySelector('.item-price').name = `items[${itemIndex}][unit_price]`;
    newRow.querySelector('.item-line-total').innerText = '$0.00';
    newRow.querySelector('.item-profit-badge').innerHTML = '';

    container.appendChild(newRow);
    itemIndex++;
    calculateTotals();
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    }
}

function handleCustomerChange(select) {
    checkCreditLimit();
}

function checkCreditLimit() {
    const select = document.getElementById('customerSelect');
    const alertBox = document.getElementById('creditLimitAlert');
    if (!select || !select.value) {
        alertBox.style.display = 'none';
        return;
    }

    const opt = select.options[select.selectedIndex];
    const limit = parseFloat(opt.getAttribute('data-credit-limit')) || 0;
    const balance = parseFloat(opt.getAttribute('data-balance')) || 0;
    const grandTotal = parseFloat(document.getElementById('grandTotalDisplay').innerText) || 0;
    const projectedTotal = balance; // In edit mode, invoice is already part of balance or replacing

    alertBox.style.display = 'block';

    if (limit <= 0) {
        alertBox.className = 'mt-2 p-2 rounded small bg-label-info text-info border border-info';
        alertBox.innerHTML = `<strong><i class="bx bx-check-shield me-1"></i>Credit Status:</strong> No Credit Limit Enforced | Current Balance Due: <strong>$${balance.toFixed(2)}</strong>`;
    } else {
        const willExceed = projectedTotal > limit;
        if (willExceed) {
            const exceededBy = projectedTotal - limit;
            alertBox.className = 'mt-2 p-2 rounded small bg-label-danger text-danger border border-danger';
            alertBox.innerHTML = `<strong><i class="bx bx-error-circle me-1"></i>Credit Limit Alert:</strong> Customer limit: <strong>$${limit.toFixed(2)}</strong> (Current Due: $${balance.toFixed(2)}). Exceeds limit by <strong class="fs-6">$${exceededBy.toFixed(2)}</strong>!`;
        } else {
            alertBox.className = 'mt-2 p-2 rounded small bg-label-success text-success border border-success';
            alertBox.innerHTML = `<strong><i class="bx bx-check-circle me-1"></i>Credit Authorized:</strong> Limit: <strong>$${limit.toFixed(2)}</strong> | Current Due: $${balance.toFixed(2)} | Available: <strong>$${(limit - balance).toFixed(2)}</strong>`;
        }
    }
}

function calculateTotals() {
    let subtotal = 0;
    let totalCost = 0;
    const rows = document.querySelectorAll('.item-row');

    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-price').value) || 0;
        const costPrice = parseFloat(row.querySelector('.item-cost').value) || 0;

        const lineTotal = qty * unitPrice;
        const lineCost = qty * costPrice;
        const lineProfit = lineTotal - lineCost;
        const marginPct = unitPrice > 0 ? ((unitPrice - costPrice) / unitPrice) * 100 : 0;

        subtotal += lineTotal;
        totalCost += lineCost;

        // Update line total display
        const totalElem = row.querySelector('.item-line-total');
        if (totalElem) {
            totalElem.innerText = '$' + lineTotal.toFixed(2);
        }

        // Update item profit badge
        const badgeElem = row.querySelector('.item-profit-badge');
        if (badgeElem) {
            if (unitPrice > 0) {
                if (lineProfit >= 0) {
                    badgeElem.innerHTML = `<span class="badge bg-label-success" title="Profit per item: $${(unitPrice - costPrice).toFixed(2)}">+${marginPct.toFixed(1)}% ($${lineProfit.toFixed(2)})</span>`;
                } else {
                    badgeElem.innerHTML = `<span class="badge bg-label-danger" title="Loss: $${Math.abs(lineProfit).toFixed(2)}">${marginPct.toFixed(1)}% (-$${Math.abs(lineProfit).toFixed(2)})</span>`;
                }
            } else {
                badgeElem.innerHTML = '';
            }
        }
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const tax = parseFloat(document.getElementById('taxInput').value) || 0;
    const grandTotal = Math.max(0, (subtotal - discount) + tax);

    // Sales Commission
    const commRate = parseFloat(document.getElementById('commissionRate').value) || 0;
    const commissionAmount = Math.max(0, (subtotal - discount) * (commRate / 100));

    // Profit calculations
    const grossProfit = Math.max(0, (subtotal - totalCost) - discount);
    const grossMarginPct = subtotal > 0 ? (grossProfit / subtotal) * 100 : 0;
    const netProfit = Math.max(0, grossProfit - commissionAmount);

    // UI Updates
    document.getElementById('subtotalDisplay').innerText = subtotal.toFixed(2);
    document.getElementById('grandTotalDisplay').innerText = grandTotal.toFixed(2);
    document.getElementById('totalCostDisplay').innerText = totalCost.toFixed(2);
    document.getElementById('grossProfitDisplay').innerText = grossProfit.toFixed(2);
    document.getElementById('grossMarginBadge').innerText = grossMarginPct.toFixed(1) + '%';
    document.getElementById('commissionAmountDisplay').innerText = '$' + commissionAmount.toFixed(2);
    document.getElementById('commissionSummaryDisplay').innerText = commissionAmount.toFixed(2);
    document.getElementById('netProfitDisplay').innerText = netProfit.toFixed(2);

    checkCreditLimit();
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
    const select = document.getElementById('customerSelect');
    if (select && select.value) {
        checkCreditLimit();
    }
});
</script>
@endsection
