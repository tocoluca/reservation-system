<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'company_id',
        'staff_code',
        'name',
        'password',
        'role',
        'is_reservable',
        'priority_order',
	'nomination_fee',
        'image_path',
        'comment',
	'force_password_change'
    ];

    protected $hidden = [
        'password',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
	public function stores()
	{
	    return $this->belongsToMany(Store::class);
	}
}