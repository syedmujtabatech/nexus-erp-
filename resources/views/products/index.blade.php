@extends('layouts.app')

@section('title', 'Products & Services Catalog - CRM Pro')

@section('content')
@php
    $canCreate = auth()->user()->hasPermission('products_create');
    $canEdit = auth()->user()->hasPermission('products_edit');
    $canDelete = auth()->user()->hasPermission('products_delete');
@endphp

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title m-0 fw-bold"><i class="bx bx-package text-primary me-2"></i>Products & Services Catalog</h5>
            <small class="text-muted">Manage items, stock inventory, purchasing cost, and selling rates</small>
        </div>
        @if($canCreate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProductModal">
                <i class="bx bx-plus me-1"></i> Add Item
            </button>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr class="table-light">
                    <th>Item Name & SKU</th>
                    <th>Type</th>
                    <th>Cost Price</th>
                    <th>Sale Price</th>
                    <th>Inventory Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($products as $prod)
                    <tr>
                        <td>
                            <strong class="text-dark d-block">{{ $prod->name }}</strong>
                            <small class="text-muted"><i class="bx bx-barcode me-1"></i>{{ $prod->code ?: 'No SKU' }}</small>
                        </td>
                        <td>
                            <span class="badge {{ $prod->type == 'product' ? 'bg-label-primary' : 'bg-label-info' }}">
                                {{ ucfirst($prod->type) }}
                            </span>
                        </td>
                        <td class="text-danger fw-semibold">${{ number_format($prod->purchase_price, 2) }}</td>
                        <td class="text-success fw-bold">${{ number_format($prod->sale_price, 2) }}</td>
                        <td>
                            @if($prod->type == 'product')
                                <span class="badge {{ $prod->stock_quantity > 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                                    {{ $prod->stock_quantity }} In Stock
                                </span>
                            @else
                                <span class="text-muted small">Service / Unlimited</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                @if($canEdit)
                                    <button class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $prod->id }}" title="Edit Product">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>
                                @endif

                                @if($canDelete)
                                    <form action="{{ route('products.destroy', $prod->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete Product">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Edit Product Modal -->
                    @if($canEdit)
                        <div class="modal fade" id="editProductModal{{ $prod->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('products.update', $prod->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-primary">
                                            <h5 class="modal-title text-white fw-bold"><i class="bx bx-edit me-2"></i>Edit Product / Service</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Item Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $prod->name }}" required>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Item Type</label>
                                                    <select name="type" class="form-select" required>
                                                        <option value="product" {{ $prod->type == 'product' ? 'selected' : '' }}>Physical Product</option>
                                                        <option value="service" {{ $prod->type == 'service' ? 'selected' : '' }}>Service</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">SKU / Code</label>
                                                    <input type="text" name="code" class="form-control" value="{{ $prod->code }}">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Cost Price ($)</label>
                                                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ $prod->purchase_price }}" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Sale Price ($)</label>
                                                    <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ $prod->sale_price }}" required>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Stock Quantity (Physical Items)</label>
                                                <input type="number" name="stock_quantity" class="form-control" value="{{ $prod->stock_quantity }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Item Description</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $prod->description }}</textarea>
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
                        <td colspan="6" class="text-center py-4 text-muted">No products or services registered yet. Click "Add Item" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Product -->
@if($canCreate)
    <div class="modal fade" id="createProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white fw-bold"><i class="bx bx-plus-circle me-2"></i>Add Product or Service</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Item Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Dell Latitude 7490 or Cloud Setup">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Item Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="product">Physical Product</option>
                                    <option value="service">Service</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">SKU / Code</label>
                                <input type="text" name="code" class="form-control" placeholder="SKU-2024">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Purchase Price / Cost ($)</label>
                                <input type="number" step="0.01" name="purchase_price" class="form-control" required placeholder="50.00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Sale Price ($)</label>
                                <input type="number" step="0.01" name="sale_price" class="form-control" required placeholder="120.00">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Initial Stock Quantity (If Product)</label>
                            <input type="number" name="stock_quantity" class="form-control" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Item specs or service scope..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
