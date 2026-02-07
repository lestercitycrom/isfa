<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductSupplierStatus;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class TenderItemSupplier extends Pivot
{
	protected $table = 'tender_item_supplier';

	public $incrementing = true;

	protected $fillable = [
		'tender_item_id',
		'supplier_id',
		'status',
		'terms',
	];

	protected $casts = [
		'status' => ProductSupplierStatus::class,
	];
}
