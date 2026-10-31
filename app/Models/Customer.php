<?php

namespace App\Models;

class Customer extends TenantModel
{
    protected $fillable = [
        'company_id',
        'created_by_user_id',
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'credit_limit',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) ($this->invoices()->sum('total') - $this->invoices()->sum('paid_amount'));
    }

    public function isCreditLimitExceeded(float $additionalAmount = 0): bool
    {
        if ($this->credit_limit <= 0) {
            return false;
        }
        return ($this->outstanding_balance + $additionalAmount) > $this->credit_limit;
    }
}
