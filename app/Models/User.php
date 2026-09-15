<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')->withPivot('role')->withTimestamps();
    }

    public function defaultCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'default_company_id');
    }

    public function getRoleInCompany(?int $companyId = null): string
    {
        if ($this->isSuperAdmin()) {
            return 'admin';
        }

        $comp = $companyId ?: ($this->default_company_id ?: 1);
        $pivot = $this->companies()->where('companies.id', $comp)->first();
        return $pivot ? $pivot->pivot->role : 'admin';
    }

    public function isAdmin(?int $companyId = null): bool
    {
        return $this->isSuperAdmin() || $this->getRoleInCompany($companyId) === 'admin';
    }

    public function canUnlockCOA(?int $companyId = null): bool
    {
        return $this->isAdmin($companyId);
    }

    public function canAccessReports(?int $companyId = null): bool
    {
        $role = $this->getRoleInCompany($companyId);
        return in_array($role, ['admin', 'accountant', 'auditor']);
    }

    public function canAccessSettings(?int $companyId = null): bool
    {
        return $this->isAdmin($companyId);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) ($this->is_superadmin || $this->email === 'superadmin@dapurgemoy.com');
    }
}
