<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class VcardTemplate extends Model
{
    protected $fillable = ['name', 'description', 'image_path', 'status'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'plan_vcard_template')->withTimestamps();
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : asset('images/default-vcard-template.svg');
    }
}
