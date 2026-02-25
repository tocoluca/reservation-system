<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'staff_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_at',
        'end_at',
        'status',
        'fingerprint'
    ];

    protected $dates = ['start_at','end_at'];

	protected $casts = [
	    'start_at' => 'datetime',
	    'end_at'   => 'datetime',
	];
}