<?php

namespace App\Models;

class Product extends TenantModel
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'type',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'description',
    ];
}
