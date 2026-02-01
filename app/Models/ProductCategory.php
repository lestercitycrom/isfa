<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductCategory extends Model
{
	use HasFactory;

	protected $fillable = [
		'company_id',
		'name',
		'description',
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
}
