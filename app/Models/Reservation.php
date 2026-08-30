<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Reservation extends Model
{
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'company_id',
        'customer_id',
        'staff_id',
        'is_staff_nominated',
        'customer_name',
        'customer_email',
        'customer_phone',
        'start_at',
        'end_at',
        'status',
        'cancelled_at',
        'cancelled_type',
        'cancelled_reason',
        'visit_reflected_at',
        'source',
        'fingerprint',
        'price',
        'nomination_fee',
        'total_price',
        'cancel_token',
        'reminder_sent_at',
		'review_token',
		'review_requested_at',
		'review_submitted_at',
    ];

    protected $dates = ['start_at', 'end_at', 'visit_reflected_at'];

    protected $casts = [
        'start_at'           => 'datetime',
        'end_at'             => 'datetime',
        'is_staff_nominated' => 'boolean',
        'cancelled_at' => 'datetime',
        'visit_reflected_at' => 'datetime',
        'reminder_sent_at'   => 'datetime',
		'review_requested_at' => 'datetime',
		'review_submitted_at' => 'datetime',
	];

    protected static function booted(): void
    {
        static::creating(function (self $reservation): void {
            if (blank($reservation->review_token)) {
                $reservation->review_token = Str::random(40);
            }
        });
    }

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
	public function review()
	{
	    return $this->hasOne(\App\Models\Review::class);
	}
    public function changeNoticeItems()
    {
        return $this->hasMany(ReservationChangeNoticeItem::class);
    }
    public function details(): HasMany
    {
        return $this->hasMany(ReservationDetail::class)->orderBy('sort_order');
    }
}
