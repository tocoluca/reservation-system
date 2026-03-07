<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{

protected $fillable = [
'company_id',
'name'
];

public function menus()
{
    return $this->hasMany(Menu::class,'menu_category_id');
}

}