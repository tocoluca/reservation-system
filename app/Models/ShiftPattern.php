<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftPattern extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'start_time',
        'end_time',
        'color'
    ];
}