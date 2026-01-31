<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderPublishHistory extends Model
{
	protected $fillable = [
		'tender_id',
		'published_at',
	];

	protected $casts = [
		'published_at' => 'datetime',
	];

	public function tender(): BelongsTo
	{
		return $this->belongsTo(Tender::class);
	}
}
