<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'staff_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_at',
        'end_at',
        'status',
        'visit_reflected_at',
        'source',
        'fingerprint',
        'price',
        'nomination_fee',
        'total_price',
        'cancel_token',
        'reminder_sent_at',
    ];

    protected $dates = ['start_at', 'end_at', 'visit_reflected_at'];

    protected $casts = [
        'start_at'           => 'datetime',
        'end_at'             => 'datetime',
        'visit_reflected_at' => 'datetime',
        'reminder_sent_at'   => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

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
            ->withPivot('price', 'duration')
            ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}