<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StylePost extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'comment',
        'image_path',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return asset($this->image_path);
    }
}