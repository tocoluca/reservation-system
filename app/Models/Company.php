<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
	protected $fillable = [
	'company_code',
	'name',
	'industry_type',
	'logo_path',
	'email',
	'phone',
	'address',
	'slot_minutes',
	'theme_color',
	'max_simultaneous_reservations',
//	'open_time',
//	'close_time',
	'regular_holidays',
	'holiday_is_closed',
	'menu_time_priority_flag',
	'is_initialized',
  	'open_patterns'
	];
	protected $casts = [
	    'open_patterns' => 'array',
	    'regular_holidays' => 'array',
	    'holiday_is_closed' => 'boolean',
	    'menu_time_priority_flag' => 'boolean',
	    'is_initialized' => 'boolean',
	];
	public function staff()
	{
	return $this->hasMany(Staff::class);
	}
}