<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\LogsCompanyActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Company extends Model
{
	use HasFactory;
	use LogsActivity;
	use LogsCompanyActivity;

	protected $fillable = [
		'name',
		'legal_name',
		'tax_id',
		'registration_number',
		'contact_name',
		'phone',
		'address',
		'website',
		'notes',
	];

	/**
	 * @return HasMany<User>
	 */
	public function users(): HasMany
	{
		return $this->hasMany(User::class);
	}

	protected function resolveActivityCompanyId(): ?int
	{
		if (! $this->exists) {
			return null;
		}

		return $this->id !== null ? (int) $this->id : null;
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->useLogName('company')
			->logFillable()
			->logOnlyDirty()
			->dontLogIfAttributesChangedOnly(['updated_at'])
			->dontSubmitEmptyLogs();
	}
}
