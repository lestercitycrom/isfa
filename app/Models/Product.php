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

final class Product extends Model
{
	use HasFactory;
	use LogsActivity;
	use LogsCompanyActivity;

	protected $fillable = [
		'company_id',
		'category_id',
		'name',
		'description',
		'comment',
	];

	/**
	 * @return BelongsTo<ProductCategory, Product>
	 */
	public function category(): BelongsTo
	{
		return $this->belongsTo(ProductCategory::class, 'category_id');
	}

	/**
	 * @return BelongsTo<User, Product>
	 */
	public function company(): BelongsTo
	{
		return $this->belongsTo(User::class, 'company_id');
	}

	/**
	 * @return BelongsToMany<Supplier>
	 */
	public function suppliers(): BelongsToMany
	{
		return $this->belongsToMany(Supplier::class, 'product_supplier')
			->using(ProductSupplier::class)
			->withPivot(['status', 'terms'])
			->withTimestamps();
	}

	/**
	 * @return BelongsToMany<Tender>
	 */
	public function tenders(): BelongsToMany
	{
		return $this->belongsToMany(Tender::class, 'tender_product')
			->withPivot(['company_id'])
			->withTimestamps();
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->useLogName('product')
			->logFillable()
			->logOnlyDirty()
			->dontLogIfAttributesChangedOnly(['updated_at'])
			->dontSubmitEmptyLogs();
	}
}
