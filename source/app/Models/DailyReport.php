<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_SUBMITTED = 'SUBMITTED';

    public const STATUS_RETURNED = 'RETURNED';

    public const STATUS_PUBLISHED = 'PUBLISHED';

    protected $fillable = [
        'author_id',
        'organization_id',
        'report_date',
        'title',
        'content',
        'status',
        'submitted_at',
        'published_at',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ReportAction::class)->latest('created_at');
    }

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'submitted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
