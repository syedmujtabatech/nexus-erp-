<?php

namespace App\Models;

class Vendor extends TenantModel
{
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class)->latest();
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class)->latest();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }
}
