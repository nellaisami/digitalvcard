<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperAdminLoginOtp extends Model
{
    protected $table = 'super_admin_login_otps';

    protected $fillable = ['super_admin_id', 'code_hash', 'expires_at', 'attempts'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(SuperAdmin::class);
    }
}
