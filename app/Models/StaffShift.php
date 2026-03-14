<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffShift extends Model
{

protected $fillable = [
'staff_id',
'date',
'shift_pattern_id',
'is_work'
];

public function pattern()
{
return $this->belongsTo(ShiftPattern::class,'shift_pattern_id');
}

}