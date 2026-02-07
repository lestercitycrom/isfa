<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class TenderKeywordSubscription extends Model
{
    protected $fillable = [
        'company_id',
        'email',
        'keyword',
        'is_active',
        'last_checked_at',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'last_checked_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Company, TenderKeywordSubscription>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return HasMany<TenderKeywordDelivery>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(TenderKeywordDelivery::class, 'subscription_id');
    }
}
