<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDefaultShift extends Model
{

protected $fillable = [
'staff_id',
'weekday',
'shift_pattern_id',
'is_work'
];

}