<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderItem extends Model
{
	protected $fillable = [
		'tender_id',
		'external_id',
		'name',
		'description',
		'unit_of_measure',
		'quantity',
		'category_code',
	];

	protected $casts = [
		'quantity' => 'decimal:4',
	];

	public function tender(): BelongsTo
	{
		return $this->belongsTo(Tender::class);
	}
}
