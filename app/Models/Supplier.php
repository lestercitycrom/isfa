<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Supplier extends Model
{
	use HasFactory;

	protected $fillable = [
		'company_id',
		'name',
		'contact_name',
		'phone',
		'email',
		'website',
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
	 * @return BelongsTo<User, Supplier>
	 */
	public function company(): BelongsTo
	{
		return $this->belongsTo(User::class, 'company_id');
	}
}
