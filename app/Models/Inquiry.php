<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'company_id',
        'category',
        'subject',
        'body',
        'status',
        'admin_reply',
        'replied_at',
        'replied_admin_id',
        'is_read_by_company',
        'company_read_at',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
        'company_read_at' => 'datetime',
        'is_read_by_company' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function repliedAdmin()
    {
        return $this->belongsTo(Admin::class, 'replied_admin_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'answered' => '回答済み',
            'closed'   => '完了',
            default    => '受付中',
        };
    }
}