<?php

namespace App\Models;

class Lead extends TenantModel
{
    protected $fillable = [
        'company_id',
        'created_by_user_id',
        'customer_id',
        'title',
        'contact_name',
        'email',
        'phone',
        'value',
        'status',
        'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
