<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class TenantModel extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->role_id != 1) { // Unless Super Admin (Role 1)
                $companyId = auth()->user()->current_company_id;
                if ($companyId) {
                    $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
                }
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->current_company_id && empty($model->company_id)) {
                $model->company_id = auth()->user()->current_company_id;
            }
        });
    }
}
