<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'company_id',
        'reservation_id',
        'customer_id',
        'rating',
        'comment',
        'nickname',
        'is_public',
        'status',
        'owner_reply',
        'owner_replied_at',
        'reviewed_at',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'owner_replied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}