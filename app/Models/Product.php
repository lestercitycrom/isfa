<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Product extends Model
{
	use HasFactory;

	protected $fillable = [
		'category_id',
		'name',
		'description',
	];

	/**
	 * @return BelongsTo<ProductCategory, Product>
	 */
	public function category(): BelongsTo
	{
		return $this->belongsTo(ProductCategory::class, 'category_id');
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
}
