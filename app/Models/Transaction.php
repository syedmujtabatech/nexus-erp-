<?php

namespace App\Models;

class Transaction extends TenantModel
{
    protected $fillable = [
        'company_id',
        'account_id',
        'to_account_id',
        'invoice_id',
        'purchase_id',
        'expense_id',
        'vendor_id',
        'created_by_user_id',
        'type',
        'amount',
        'date',
        'description',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
