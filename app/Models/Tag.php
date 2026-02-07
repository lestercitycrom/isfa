<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Tag extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
    ];

    /**
     * @return BelongsTo<Company, Tag>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * @return BelongsToMany<Supplier>
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_tag')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Tender>
     */
    public function tenders(): BelongsToMany
    {
        return $this->belongsToMany(Tender::class, 'tag_tender')
            ->withTimestamps();
    }
}
