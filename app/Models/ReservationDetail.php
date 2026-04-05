<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationDetail extends Model
{
    protected $fillable = [
        'reservation_id',
        'menu_id',
        'staff_id',
        'start_at',
        'end_at',
        'duration',
        'price',
        'sort_order',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'duration' => 'integer',
        'price' => 'integer',
        'sort_order' => 'integer',
    ];

    public function reservation()
    {
        return $this->belongsTo(\App\Models\Reservation::class);
    }

    public function menu()
    {
        return $this->belongsTo(\App\Models\Menu::class);
    }

    public function staff()
    {
        return $this->belongsTo(\App\Models\Staff::class);
    }
}