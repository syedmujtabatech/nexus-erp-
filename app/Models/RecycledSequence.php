<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecycledSequence extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'sequence_number',
    ];
}
