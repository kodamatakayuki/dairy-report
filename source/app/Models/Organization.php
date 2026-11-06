<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    public const LEVEL_BOARD = 'BOARD';

    public const LEVEL_HEADQUARTERS = 'HQ';

    public const LEVEL_DEPARTMENT = 'DEPARTMENT';

    public const LEVEL_GROUP = 'GROUP';

    public const LEVELS = [
        self::LEVEL_BOARD => '取締役会',
        self::LEVEL_HEADQUARTERS => '本部',
        self::LEVEL_DEPARTMENT => '部',
        self::LEVEL_GROUP => 'グループ',
    ];

    public const PARENT_LEVELS = [
        self::LEVEL_HEADQUARTERS => self::LEVEL_BOARD,
        self::LEVEL_DEPARTMENT => self::LEVEL_HEADQUARTERS,
        self::LEVEL_GROUP => self::LEVEL_DEPARTMENT,
    ];

    protected $fillable = ['name', 'level', 'parent_id', 'is_active'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function leaders(): HasMany
    {
        return $this->users()->where('is_leader', true);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function includes(Organization $organization): bool
    {
        for ($current = $organization; $current !== null; $current = $current->parent) {
            if ($current->is($this)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public function subtreeIds(): array
    {
        $ids = [$this->id];
        $parentIds = $ids;

        while ($parentIds !== []) {
            $parentIds = self::query()
                ->whereIn('parent_id', $parentIds)
                ->pluck('id')
                ->all();

            $ids = [...$ids, ...$parentIds];
        }

        return $ids;
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
