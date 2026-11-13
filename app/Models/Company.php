<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'currency',
        'logo',
        'plan',
        'max_users',
        'max_invoices',
        'plan_expires_at',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user')->withPivot('role_id', 'permissions');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
