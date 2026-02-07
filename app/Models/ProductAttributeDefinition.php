<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductAttributeDefinition extends Model
{
	use HasFactory;

	protected $fillable = [
		'company_id',
		'code',
		'label',
		'field_type',
		'options_json',
		'sort_order',
		'is_active',
	];

	protected $casts = [
		'options_json' => 'array',
		'is_active' => 'bool',
		'sort_order' => 'int',
	];

	/**
	 * @return BelongsTo<Company, ProductAttributeDefinition>
	 */
	public function company(): BelongsTo
	{
		return $this->belongsTo(Company::class);
	}

	/**
	 * @return HasMany<ProductAttributeValue>
	 */
	public function values(): HasMany
	{
		return $this->hasMany(ProductAttributeValue::class);
	}
}

