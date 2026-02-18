<?php

namespace App\Models;

use App\Contracts\HasChangeHistory;
use App\Enums\LogAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function unit(): HasOne
    {
        return $this->hasOne(OrgUnit::class, 'id_user');
    }

    /** @return HasMany<ActivityLog, $this> */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /** @return HasMany<ChangeHistory, $this> */
    public function changesMade(): HasMany
    {
        return $this->hasMany(ChangeHistory::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function recordCreation(Model $model, string $reason): void
    {
        try {
            $this->activityLogs()->create([
                'action' => LogAction::Create,
                'message' => $this->buildMessage('Membuat', $model),
                'metadata' => [
                    'model_type' => $model::class,
                    'model_id' => $model->getKey(),
                ],
            ]);

            if ($model instanceof HasChangeHistory) {
                $change = $model->changes()->make([
                    'change_reason' => $reason,
                    'state_before' => null,
                    'state_after' => $this->snapshotAttributes($model),
                ]);
                $change->user()->associate($this);
                $change->save();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to record creation activity', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record a change to a model. Must be called BEFORE save().
     */
    public function recordChange(Model $model, string $reason): void
    {
        try {
            $dirtyFields = array_keys($model->getDirty());

            $this->activityLogs()->create([
                'action' => LogAction::Update,
                'message' => $this->buildMessage('Mengubah', $model),
                'metadata' => [
                    'model_type' => $model::class,
                    'model_id' => $model->getKey(),
                    'changed_fields' => $dirtyFields,
                ],
            ]);

            if ($model instanceof HasChangeHistory && count($dirtyFields) > 0) {
                $stateBefore = collect($model->getOriginal())
                    ->only($dirtyFields)
                    ->all();

                $stateAfter = collect($model->getDirty())
                    ->all();

                $change = $model->changes()->make([
                    'change_reason' => $reason,
                    'state_before' => $stateBefore,
                    'state_after' => $stateAfter,
                ]);
                $change->user()->associate($this);
                $change->save();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to record change activity', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Record a model deletion. Must be called BEFORE delete().
     */
    public function recordDeletion(Model $model, string $reason): void
    {
        try {
            $this->activityLogs()->create([
                'action' => LogAction::Delete,
                'message' => $this->buildMessage('Menghapus', $model),
                'metadata' => [
                    'model_type' => $model::class,
                    'model_id' => $model->getKey(),
                ],
            ]);

            if ($model instanceof HasChangeHistory) {
                $change = $model->changes()->make([
                    'change_reason' => $reason,
                    'state_before' => $this->snapshotAttributes($model),
                    'state_after' => null,
                ]);
                $change->user()->associate($this);
                $change->save();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to record deletion activity', ['error' => $e->getMessage()]);
        }
    }

    private function buildMessage(string $action, Model $model): string
    {
        $label = match ($model::class) {
            Grant::class => 'hibah',
            Donor::class => 'pemberi hibah',
            OrgUnit::class => 'unit',
            OrgUnitChief::class => 'kepala unit',
            self::class => 'pengguna',
            default => Str::lower(class_basename($model)),
        };

        $identifier = match ($model::class) {
            Grant::class => $model->nama_hibah,
            Donor::class => $model->nama,
            OrgUnit::class => $model->nama_unit,
            OrgUnitChief::class => $model->nama_lengkap,
            self::class => $model->name,
            default => (string) $model->getKey(),
        };

        return "{$action} {$label}: {$identifier}";
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotAttributes(Model $model): array
    {
        $excluded = [
            'id', 'created_at', 'updated_at', 'deleted_at',
            'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
        ];

        return collect($model->attributesToArray())
            ->except($excluded)
            ->all();
    }
}
