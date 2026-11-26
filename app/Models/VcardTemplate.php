<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VcardTemplate extends Model
{
    protected $fillable = ['name', 'description', 'image_path', 'image_url', 'status'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_vcard_template')->withTimestamps();
    }

    public function getImageUrlAttribute(): string
    {
        if (! $this->image_path) {
            return asset('images/default-vcard-template.svg');
        }

        $imageUrl = $this->attributes['image_url'] ?? 'storage/'.$this->image_path;

        return asset(ltrim($imageUrl, '/'));
    }
}
