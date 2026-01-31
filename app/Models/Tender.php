<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tender extends Model
{
	protected $fillable = [
		'event_id',
		'rfx_id',
		'inner_event_id',
		'title',
		'organization_name',
		'organization_voen',
		'address',
		'document_number',
		'document_version',
		'event_type_code',
		'event_status_code',
		'document_view_type_code',
		'estimated_amount',
		'min_number_of_suppliers',
		'published_at',
		'start_at',
		'end_at',
		'envelope_at',
		'view_fee',
		'participation_fee',
		'raw',
	];

	protected $casts = [
		'published_at' => 'datetime',
		'start_at' => 'datetime',
		'end_at' => 'datetime',
		'envelope_at' => 'datetime',
		'raw' => 'array',
	];

	public function items(): HasMany
	{
		return $this->hasMany(TenderItem::class);
	}

	public function contacts(): HasMany
	{
		return $this->hasMany(TenderContact::class);
	}

	public function announcements(): HasMany
	{
		return $this->hasMany(TenderAnnouncement::class);
	}

	public function publishHistories(): HasMany
	{
		return $this->hasMany(TenderPublishHistory::class);
	}
}
