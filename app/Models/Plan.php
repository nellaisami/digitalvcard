<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'frequency',
        'trial_days',
        'features',
        'feature_limits',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'trial_days' => 'integer',
            'features' => 'array',
            'feature_limits' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }

    public function vcardTemplates(): BelongsToMany
    {
        return $this->belongsToMany(VcardTemplate::class, 'plan_vcard_template')->withTimestamps();
    }
}
