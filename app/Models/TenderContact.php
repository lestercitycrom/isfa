<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderContact extends Model
{
	protected $fillable = [
		'tender_id',
		'full_name',
		'position',
		'contact',
		'phone_number',
	];

	public function tender(): BelongsTo
	{
		return $this->belongsTo(Tender::class);
	}
}
