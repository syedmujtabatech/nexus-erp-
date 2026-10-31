<?php

namespace App\Models;

use App\Services\SequenceService;

class Quotation extends TenantModel
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'created_by_user_id',
        'quotation_number',
        'sequence_number',
        'date',
        'valid_until',
        'subtotal',
        'discount',
        'tax',
        'total',
        'status',
        'notes',
    ];

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($quotation) {
            $quotation->items()->delete();
            SequenceService::recycleNumber($quotation->company_id, 'quotation', $quotation->sequence_number);
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->morphMany(InvoiceItem::class, 'itemable');
    }
}
