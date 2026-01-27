<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\LowercaseCast;
use App\Casts\TitleCaseCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read Pilot|null $pilot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CarTechInspectionHistory> $techInspections
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
