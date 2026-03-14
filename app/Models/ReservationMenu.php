<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationMenu extends Model
{
    protected $fillable = [
        'reservation_id',
        'menu_id',
        'price',
        'duration'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}