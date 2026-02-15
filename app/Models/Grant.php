<?php

namespace App\Models;

use App\Concerns\HasChangeHistory as HasChangeHistoryTrait;
use App\Concerns\HasFiles as HasFilesTrait;
use App\Contracts\HasChangeHistory;
use App\Contracts\HasFiles;
use App\Enums\GrantForm;
use App\Enums\GrantStage;
use App\Enums\GrantType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grant extends Model implements HasChangeHistory, HasFiles
{
    /** @use HasFactory<\Database\Factories\GrantFactory> */
    use HasChangeHistoryTrait, HasFactory, HasFilesTrait, SoftDeletes;

    protected $table = 'hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_pemberi_hibah',
        'nama_hibah',
        'jenis_hibah',
        'tahapan',
        'bentuk_hibah',
        'nilai_hibah',
        'mata_uang',
        'ada_usulan',
        'nomor_surat_dari_calon_pemberi_hibah',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_hibah' => GrantType::class,
            'tahapan' => GrantStage::class,
            'bentuk_hibah' => GrantForm::class,
            'ada_usulan' => 'boolean',
            'nilai_hibah' => 'decimal:2',
        ];
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'id_satuan_kerja', 'id_user');
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(Donor::class, 'id_pemberi_hibah');
    }

    public function withdrawalPlans(): HasMany
    {
        return $this->hasMany(GrantWithdrawalPlan::class, 'id_hibah');
    }

    public function locationAllocations(): HasMany
    {
        return $this->hasMany(GrantLocationAndAllocation::class, 'id_hibah');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(GrantStatusHistory::class, 'id_hibah');
    }

    public function budgetPlans(): HasMany
    {
        return $this->hasMany(GrantBudgetPlan::class, 'id_hibah');
    }

    public function information(): HasMany
    {
        return $this->hasMany(GrantInformation::class, 'id_hibah');
    }

    public function financeMinistrySubmission(): HasOne
    {
        return $this->hasOne(GrantFinanceMinistrySubmission::class, 'id_hibah');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GrantDocument::class, 'id_hibah');
    }

    public function activitySchedules(): HasMany
    {
        return $this->hasMany(ActivitySchedule::class, 'id_hibah');
    }

    public function numberings(): HasMany
    {
        return $this->hasMany(GrantNumbering::class, 'id_hibah');
    }
}
