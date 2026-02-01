<?php

namespace App\Models;

use App\Concerns\LogsCompanyActivity;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;
    use LogsActivity;
    use LogsCompanyActivity;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_COMPANY = 'company';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_name',
        'legal_name',
        'tax_id',
        'registration_number',
        'contact_name',
        'phone',
        'address',
        'website',
        'notes',
        'password_plain',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_plain',
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
            'password_plain' => 'encrypted',
        ];
    }

    /**
     * Determine whether user is an admin.
     */
    public function isAdmin(): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        if ($this->role === null) {
            $adminEmail = (string) env('ADMIN_EMAIL', '');
            if ($adminEmail !== '' && $this->email === $adminEmail) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope only company users.
     */
    public function scopeCompanies($query)
    {
        return $query->where('role', self::ROLE_COMPANY);
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

	protected function resolveActivityCompanyId(): ?int
	{
		if ($this->isAdmin()) {
			return null;
		}

		return (int) $this->id;
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->useLogName('user')
			->logFillable()
			->logOnlyDirty()
			->logExcept([
				'password',
				'password_plain',
				'remember_token',
				'two_factor_secret',
				'two_factor_recovery_codes',
			])
			->dontLogIfAttributesChangedOnly(['updated_at'])
			->dontSubmitEmptyLogs();
	}
}
