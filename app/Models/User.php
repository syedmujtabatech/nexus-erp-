<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'role_id',
        'otp',
        'is_otp_verified',
        'current_company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')->withPivot('role_id', 'permissions');
    }

    public function currentCompany()
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    public function isSuperAdmin()
    {
        return $this->role_id == 1;
    }

    public function isCompanyOwner()
    {
        return $this->role_id == 2;
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isSuperAdmin() || $this->isCompanyOwner()) {
            return true;
        }

        $membership = $this->companies()->where('company_id', $this->current_company_id)->first();
        if (!$membership || empty($membership->pivot->permissions)) {
            return false;
        }

        $perms = json_decode($membership->pivot->permissions, true) ?? [];
        return in_array($permissionKey, $perms);
    }
}
