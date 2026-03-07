<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

protected $fillable = [
'company_id',
'menu_category_id',
'name',
'description',
'duration',
'price'
];

public function category()
{
    return $this->belongsTo(MenuCategory::class,'menu_category_id');
}
public function tags()
{
    return $this->belongsToMany(MenuTag::class,'menu_tag_menu');
}
}