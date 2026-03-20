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
        'source',
        'fingerprint',
	    // 追加
	    'price',
	    'nomination_fee',
	    'total_price',
	'cancel_token'
    ];

    protected $dates = ['start_at','end_at'];

	protected $casts = [
	    'start_at' => 'datetime',
	    'end_at'   => 'datetime',
	];

	public function staff()
	{
	    return $this->belongsTo(\App\Models\Staff::class);
	}

	public function reservationMenus()
	{
	    return $this->hasMany(ReservationMenu::class);
	}
	public function menus()
	{
	    return $this->belongsToMany(Menu::class, 'reservation_menus')
	        ->withPivot('price','duration')
	        ->withTimestamps();
	}
}