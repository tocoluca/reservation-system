<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuTag extends Model
{

protected $fillable = [
'company_id',
'name'
];

public function menus()
{
    return $this->belongsToMany(Menu::class,'menu_tag_menu');
}

}