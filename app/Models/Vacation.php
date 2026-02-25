<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    protected $fillable = [
        'staff_id',
        'start_at',
        'end_at',
	'is_full_day',
        'status'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}