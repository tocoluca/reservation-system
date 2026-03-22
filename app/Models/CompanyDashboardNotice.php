<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CompanyDashboardNotice extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image',
        'is_new',
        'is_important',
        'is_active',
        'target_type',
        'company_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_new'       => 'boolean',
        'is_important' => 'boolean',
        'is_active'    => 'boolean',
        'start_date'   => 'date',
        'end_date'     => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeVisibleForCompany(Builder $query, int $companyId): Builder
    {
        $today = Carbon::today();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($companyId) {
                $q->where('target_type', 'all')
                  ->orWhere(function ($q2) use ($companyId) {
                      $q2->where('target_type', 'company')
                         ->where('company_id', $companyId);
                  });
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')
                  ->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')
                  ->orWhereDate('end_date', '>=', $today);
            });
    }

    public function getTargetLabelAttribute(): string
    {
        if ($this->target_type === 'all') {
            return '全企業向け';
        }

        return optional($this->company)->name
            ? '特定企業向け（' . $this->company->name . '）'
            : '特定企業向け';
    }
}