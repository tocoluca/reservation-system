<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{

protected $fillable=[
'customer_id',
'staff_id',
'note'
];

public function customer()
{
return $this->belongsTo(Customer::class);
}

}