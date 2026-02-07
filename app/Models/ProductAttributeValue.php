<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductAttributeValue extends Model
{
	use HasFactory;

	protected $fillable = [
		'product_id',
		'product_attribute_definition_id',
		'value_text',
		'value_json',
	];

	protected $casts = [
		'value_json' => 'array',
	];

	/**
	 * @return BelongsTo<Product, ProductAttributeValue>
	 */
	public function product(): BelongsTo
	{
		return $this->belongsTo(Product::class);
	}

	/**
	 * @return BelongsTo<ProductAttributeDefinition, ProductAttributeValue>
	 */
	public function definition(): BelongsTo
	{
		return $this->belongsTo(ProductAttributeDefinition::class, 'product_attribute_definition_id');
	}
}

