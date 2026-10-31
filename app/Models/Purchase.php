<?php

namespace App\Models;

use App\Services\SequenceService;

class Purchase extends TenantModel
{
    protected $fillable = [
        'company_id',
        'vendor_id',
        'created_by_user_id',
        'purchase_number',
        'sequence_number',
        'date',
        'due_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'paid_amount',
        'status',
        'notes',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($purchase) {
            // Revert product stock
            foreach ($purchase->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock_quantity', $item->quantity);
                    }
                }
            }
            $purchase->items()->delete();
            $purchase->transactions()->delete();
            SequenceService::recycleNumber($purchase->company_id, 'purchase', $purchase->sequence_number);
        });
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
