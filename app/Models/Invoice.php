<?php

namespace App\Models;

use App\Services\SequenceService;

class Invoice extends TenantModel
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'quotation_id',
        'created_by_user_id',
        'sales_person_id',
        'invoice_number',
        'sequence_number',
        'date',
        'due_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid_amount',
        'status',
        'notes',
        'commission_rate',
        'commission_amount',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($invoice) {
            $invoice->items()->delete();
            SequenceService::recycleNumber($invoice->company_id, 'invoice', $invoice->sequence_number);
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function items()
    {
        return $this->morphMany(InvoiceItem::class, 'itemable');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->items->sum(function ($item) {
            return $item->effective_cost_price * $item->quantity;
        });
    }

    public function getTotalProfitAttribute(): float
    {
        return (float) max(0, ($this->subtotal - $this->total_cost) - $this->discount);
    }

    public function getNetProfitAttribute(): float
    {
        return (float) max(0, $this->total_profit - ($this->commission_amount ?? 0));
    }

    public function getGrossMarginPercentageAttribute(): float
    {
        if ($this->subtotal <= 0) {
            return 0.0;
        }
        return round(($this->total_profit / $this->subtotal) * 100, 1);
    }
}
