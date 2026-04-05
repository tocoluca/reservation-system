<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationChangeNotice extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'target_date',
        'reason_type',
        'reason_text',
        'status',
        'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(ReservationChangeNoticeItem::class, 'notice_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}