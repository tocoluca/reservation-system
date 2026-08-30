<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'image_path',
    ];

    public static function fallbackImagePath(string $name): string
    {
        return match ($name) {
            'カット' => 'images/menu-icons/cut.jpg',
            'カラー' => 'images/menu-icons/color.jpg',
            '白髪染め' => 'images/menu-icons/graycolor.jpg',
            'リタッチ' => 'images/menu-icons/retouch.jpg',
            'パーマ' => 'images/menu-icons/perm.jpg',
            '縮毛矯正' => 'images/menu-icons/straight.jpg',
            'コンディショナー' => 'images/menu-icons/conditioner.jpg',
            'トリートメント' => 'images/menu-icons/treatment.jpg',
            'ヘッドスパ' => 'images/menu-icons/headspa.jpg',
            'セット・ヘアアレンジ', 'ヘアアレンジ' => 'images/menu-icons/hairset.jpg',
            'メンズ' => 'images/menu-icons/mens.jpg',
            '前髪カット' => 'images/menu-icons/bangcut.jpg',
            '着付け' => 'images/menu-icons/kitsuke.jpg',
            'まつげ', '眉' => 'images/menu-icons/eyelash_brow.jpg',
            'フェイシャル' => 'images/menu-icons/facial.jpg',
            '整体' => 'images/menu-icons/seitai.jpg',
            'キッズ' => 'images/menu-icons/kids.jpg',
            default => 'images/menu-icons/other.jpg',
        };
    }

    public static function fallbackImageUrl(string $name): string
    {
        return asset(static::fallbackImagePath($name));
    }

    public function getDisplayImageUrlAttribute(): string
    {
        return $this->image_path
            ? asset($this->image_path)
            : static::fallbackImageUrl($this->name);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_category_id');
    }
}
