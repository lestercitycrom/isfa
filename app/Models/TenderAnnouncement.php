<?php

namespace App\Models;

use App\Concerns\LogsCompanyActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TenderAnnouncement extends Model
{
	use LogsActivity;
	use LogsCompanyActivity;

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

	protected function resolveActivityCompanyId(): ?int
	{
		$tender = $this->relationLoaded('tender') ? $this->tender : null;

		if ($tender instanceof Tender) {
			return $tender->company_id !== null ? (int) $tender->company_id : null;
		}

		if ($this->tender_id === null) {
			return null;
		}

		$companyId = Tender::query()
			->whereKey($this->tender_id)
			->value('company_id');

		return $companyId !== null ? (int) $companyId : null;
	}

	public function getActivitylogOptions(): LogOptions
	{
		return LogOptions::defaults()
			->useLogName('tender_announcement')
			->logFillable()
			->logOnlyDirty()
			->dontLogIfAttributesChangedOnly(['updated_at'])
			->dontSubmitEmptyLogs();
	}
}
