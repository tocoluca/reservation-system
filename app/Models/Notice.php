<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notice extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'content',
        'image',
        'start_date',
        'end_date',
        'is_active',
        'is_important',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // 表示対象
    public function scopeVisible($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            });
    }

    // 並び順
    public function scopeSorted($query)
    {
        return $query
            ->orderByDesc('is_important')
            ->orderByDesc('created_at');
    }

    // NEW判定
    public function isNew()
    {
        return $this->created_at >= Carbon::now()->subDays(3);
    }
}