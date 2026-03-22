<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = 'company_applications';

    protected $fillable = [
        'company_name',
        'industry_type',
        'contact_person',
        'email',
        'phone',
        'message',
        'status',
        'reject_reason',
        'reviewed_at',
        'approved_company_id',
        'initial_staff_code',
        'initial_password_plain',
        'login_url',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function approvedCompany()
    {
        return $this->belongsTo(Company::class, 'approved_company_id');
    }

    public function getIndustryLabelAttribute()
    {
        return match ($this->industry_type) {
            'beauty' => '美容',
            'dental' => '歯科',
            default  => $this->industry_type,
        };
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'pending'  => '審査待ち',
            'approved' => '承認済',
            'rejected' => '却下',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending'  => 'bg-amber-100 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-red-100 text-red-700 border-red-200',
            default    => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }
}