<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductSupplierStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class ProductSupplier extends Pivot
{
	protected $table = 'product_supplier';

	public $incrementing = true;

	protected $fillable = [
		'product_id',
		'supplier_id',
		'status',
		'terms',
	];

	protected $casts = [
		'status' => ProductSupplierStatus::class,
	];
}
