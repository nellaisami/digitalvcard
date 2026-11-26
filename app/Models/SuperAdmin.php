<?php

namespace App\Models;

use Database\Factories\SuperAdminFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    /** @use HasFactory<SuperAdminFactory> */
    use HasFactory, Notifiable;

    protected $table = 'super_admins';

    protected $fillable = [
        'name', 'username', 'email', 'password', 'contact_no', 'address', 'city',
        'country', 'postal_code', 'status', 'email_verified_at', 'profile_photo',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function loginOtp(): HasOne
    {
        return $this->hasOne(SuperAdminLoginOtp::class);
    }

    public function getRoleAttribute(): string
    {
        return 'superadmin';
    }

    public function getProfilePhotoUrlAttribute(): string
    {
        return $this->profile_photo
            ? asset('storage/'.$this->profile_photo)
            : asset('images/default-avatar.svg');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
