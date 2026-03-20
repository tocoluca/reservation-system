<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable=[
        'company_id',
        'name',
        'phone',
        'email',
        'visit_count',
        'last_visit',
        'memo',
        'photo'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function notes()
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function photos()
    {
        return $this->hasMany(CustomerPhoto::class);
    }
}