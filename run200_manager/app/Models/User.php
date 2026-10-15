<?php

namespace App\Models;

use App\Casts\LowercaseCast;
use App\Casts\TitleCaseCast;
use Database\Factories\UserFactory;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Throwable;

/**
 * @property-read Pilot|null $pilot
 * @property-read Collection<int, CarTechInspectionHistory> $techInspections
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            // Formatage des données
            'name' => TitleCaseCast::class,
            'email' => LowercaseCast::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * A mail transport failure must not make a completed registration look failed.
     */
    public function sendEmailVerificationNotification(): bool
    {
        try {
            $this->notify(new VerifyEmail);

            return true;
        } catch (Throwable $e) {
            Log::error('Email verification notification delivery failed', [
                'user_id' => $this->id,
                'error_type' => $e::class,
            ]);

            return false;
        }
    }

    /**
     * Configure activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Check if user has pilot role
     */
    public function isPilot(): bool
    {
        return $this->hasRole('PILOTE');
    }

    /**
     * Check if user has any staff role
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole([
            'STAFF_ADMINISTRATIF',
            'CONTROLEUR_TECHNIQUE',
            'STAFF_ENTREE',
            'STAFF_SONO',
        ]);
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('ADMIN');
    }

    /**
     * Get the pilot profile associated with the user.
     *
     * @return HasOne<Pilot, $this>
     */
    public function pilot(): HasOne
    {
        return $this->hasOne(Pilot::class);
    }

    /**
     * Get the tech inspections performed by this user.
     *
     * @return HasMany<CarTechInspectionHistory, $this>
     */
    public function techInspections(): HasMany
    {
        return $this->hasMany(CarTechInspectionHistory::class, 'inspected_by');
    }
}
