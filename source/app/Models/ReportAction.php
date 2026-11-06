<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAction extends Model
{
    public const UPDATED_AT = null;

    public const TYPE_COMMENT = 'COMMENT';

    public const TYPE_RETURN = 'RETURN';

    public const TYPE_PUBLISH = 'PUBLISH';

    protected $fillable = ['daily_report_id', 'actor_id', 'action_type', 'comment_text'];

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
