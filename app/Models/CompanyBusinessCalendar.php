<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyBusinessCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'date',
        'is_open',
        'open_time',
        'close_time',
    ];

    protected $casts = [
        'date' => 'date',
        'is_open' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | リレーション
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}