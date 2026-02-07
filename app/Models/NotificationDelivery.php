<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NotificationDelivery extends Model
{
	protected $fillable = [
		'company_id',
		'user_id',
		'tender_id',
		'template_key',
		'reminder_type',
		'recipient_email',
		'subject',
		'body',
		'deadline_at',
		'sent_at',
		'status',
		'error_message',
		'meta',
	];

	protected $casts = [
		'deadline_at' => 'datetime',
		'sent_at' => 'datetime',
		'meta' => 'array',
	];

	/**
	 * @return BelongsTo<User, NotificationDelivery>
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * @return BelongsTo<Tender, NotificationDelivery>
	 */
	public function tender(): BelongsTo
	{
		return $this->belongsTo(Tender::class);
	}

	/**
	 * @return BelongsTo<Company, NotificationDelivery>
	 */
	public function company(): BelongsTo
	{
		return $this->belongsTo(Company::class);
	}
}
