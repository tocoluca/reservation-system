<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationChangeNoticeItem extends Model
{
    protected $fillable = [
        'notice_id',
        'company_id',
        'reservation_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'contact_type',
        'contact_status',
        'response_status',
        'response_token',
        'mail_sent_at',
        'last_reminder_sent_at',
        'reminder_send_count',
        'mail_opened_at',
        'confirmed_at',
        'called_at',
        'cancelled_at',
        'cancel_reason_type',
        'cancel_processed_by',
        'updated_by',
        'note',
    ];

    protected $casts = [
        'mail_sent_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'mail_opened_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'called_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function notice()
    {
        return $this->belongsTo(ReservationChangeNotice::class, 'notice_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isConfirmed(): bool
    {
        return !is_null($this->confirmed_at)
            || in_array($this->response_status, ['confirmed', 'phone_confirmed', 'closed'], true);
    }
}