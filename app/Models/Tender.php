<?php

namespace App\Models;

use App\Concerns\LogsCompanyActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tender extends Model
{
    use LogsActivity;
    use LogsCompanyActivity;

    protected $fillable = [
        'company_id',
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
        'comment',
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

    /**
     * @return BelongsTo<Company, Tender>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
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

    public function notificationDeliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    /**
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'tender_product')
            ->withPivot(['company_id'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Tag>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_tender')
            ->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('tender')
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['raw'])
            ->dontLogIfAttributesChangedOnly(['updated_at'])
            ->dontSubmitEmptyLogs();
    }
}
