<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFollowupMailLog extends Model
{
    protected $fillable = [
        'company_id',
        'customer_id',
        'mail_type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}