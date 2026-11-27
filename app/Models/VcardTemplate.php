<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VcardTemplate extends Model
{
    protected $fillable = ['name', 'price', 'html_code', 'image_path', 'image_url', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_vcard_template')->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
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
