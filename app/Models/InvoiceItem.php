<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'itemable_id',
        'itemable_type',
        'product_id',
        'description',
        'quantity',
        'cost_price',
        'unit_price',
        'total_price',
    ];

    public function itemable()
    {
        return $this->morphTo();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectiveCostPriceAttribute(): float
    {
        if ($this->cost_price > 0) {
            return (float) $this->cost_price;
        }
        return (float) ($this->product->purchase_price ?? 0);
    }

    public function getProfitAttribute(): float
    {
        return (float) (($this->unit_price - $this->effective_cost_price) * $this->quantity);
    }

    public function getMarginPercentageAttribute(): float
    {
        if ($this->unit_price <= 0) {
            return 0.0;
        }
        return round((($this->unit_price - $this->effective_cost_price) / $this->unit_price) * 100, 1);
    }
}
