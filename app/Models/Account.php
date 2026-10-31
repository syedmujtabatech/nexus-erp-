<?php

namespace App\Models;

class Account extends TenantModel
{
    protected $fillable = [
        'company_id',
        'name',
        'account_number',
        'opening_balance',
        'current_balance',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }
}
