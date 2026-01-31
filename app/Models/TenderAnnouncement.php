<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderAnnouncement extends Model
{
	protected $fillable = [
		'tender_id',
		'announcement_version',
		'external_id',
		'text',
	];

	public function tender(): BelongsTo
	{
		return $this->belongsTo(Tender::class);
	}
}
