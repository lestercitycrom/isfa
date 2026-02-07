<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenderKeywordDelivery extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'event_id',
        'event_title',
        'event_url',
        'recipient_email',
        'status',
        'sent_at',
        'error_message',
        'payload',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'payload' => 'array',
    ];

    /**
     * @return BelongsTo<Company, TenderKeywordDelivery>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return BelongsTo<TenderKeywordSubscription, TenderKeywordDelivery>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenderKeywordSubscription::class, 'subscription_id');
    }
}
