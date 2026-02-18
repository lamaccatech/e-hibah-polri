<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ChangeHistory extends Model
{
    /** @use HasFactory<\Database\Factories\ChangeHistoryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'change_reason',
        'state_before',
        'state_after',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'state_before' => 'array',
            'state_after' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function changeable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Compute a diff between state_before and state_after.
     *
     * @return array<string, array{from: mixed, to: mixed}>
     */
    public function getChanges(): array
    {
        $before = $this->state_before;
        $after = $this->state_after;

        if ($before === null && $after === null) {
            return [];
        }

        if ($before === null) {
            return collect($after)
                ->map(fn (mixed $value) => ['from' => null, 'to' => $value])
                ->all();
        }

        if ($after === null) {
            return collect($before)
                ->map(fn (mixed $value) => ['from' => $value, 'to' => null])
                ->all();
        }

        $changes = [];
        $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));

        foreach ($allKeys as $key) {
            $oldValue = $before[$key] ?? null;
            $newValue = $after[$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$key] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        return $changes;
    }
}
