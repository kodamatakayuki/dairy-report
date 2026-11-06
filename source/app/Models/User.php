<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'login_id', 'system_role', 'is_leader', 'organization_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_ADMIN = 'ADMIN';

    public const ROLE_GENERAL = 'GENERAL';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'author_id');
    }

    public function reportActions(): HasMany
    {
        return $this->hasMany(ReportAction::class, 'actor_id');
    }

    public function isAdmin(): bool
    {
        return $this->system_role === self::ROLE_ADMIN;
    }

    public function isLeader(): bool
    {
        return $this->is_leader;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_leader' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
