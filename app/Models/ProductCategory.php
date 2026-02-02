<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\LogsCompanyActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class ProductCategory extends Model
{
	use HasFactory;
	use LogsActivity;
	use LogsCompanyActivity;

	protected $fillable = [
		'company_id',
		'name',
		'description',
		'comment',
	];

	/**
	 * @return HasMany<Product>
	 */
	public function products(): HasMany
	{
		return $this->hasMany(Product::class, 'category_id');
	}

	/**
	 * @return BelongsTo<User, ProductCategory>
	 */
	public function company(): BelongsTo
	{
		return $this->belongsTo(User::class, 'company_id');
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->useLogName('category')
			->logFillable()
			->logOnlyDirty()
			->dontLogIfAttributesChangedOnly(['updated_at'])
			->dontSubmitEmptyLogs();
	}
}
