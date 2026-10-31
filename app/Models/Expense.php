<?php

namespace App\Models;

class Expense extends TenantModel
{
    protected $fillable = [
        'company_id',
        'vendor_id',
        'account_id',
        'created_by_user_id',
        'category',
        'amount',
        'date',
        'reference',
        'description',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
