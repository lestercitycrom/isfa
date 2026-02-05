<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\LogsCompanyActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class Supplier extends Model
{
	use HasFactory;
	use LogsActivity;
	use LogsCompanyActivity;

	protected $fillable = [
		'company_id',
		'name',
		'contact_name',
		'phone',
		'email',
		'website',
		'photo_path',
		'comment',
	];

	/**
	 * @return BelongsToMany<Product>
	 */
	public function products(): BelongsToMany
	{
		return $this->belongsToMany(Product::class, 'product_supplier')
			->using(ProductSupplier::class)
			->withPivot(['status', 'terms'])
			->withTimestamps();
	}

	/**
	 * @return BelongsTo<Company, Supplier>
	 */
	public function company(): BelongsTo
	{
		return $this->belongsTo(Company::class, 'company_id');
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->useLogName('supplier')
			->logFillable()
			->logOnlyDirty()
			->dontLogIfAttributesChangedOnly(['updated_at'])
			->dontSubmitEmptyLogs();
	}
}
