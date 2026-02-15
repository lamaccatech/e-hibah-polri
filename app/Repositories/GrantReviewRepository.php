<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Collection;

class GrantReviewRepository
{
    /** @return Collection<int, Grant> */
    public function allSubmittedToUnit(OrgUnit $unit): Collection
    {
        $childUnitUserIds = $unit->children()->pluck('id_user');

        return Grant::query()
            ->whereIn('id_satuan_kerja', $childUnitUserIds)
            ->where('tahapan', GrantStage::Planning)
            ->whereHas('statusHistory', function ($query): void {
                $query->where('status_sesudah', GrantStatus::PlanningSubmittedToPolda)
                    ->orWhere('status_sesudah', GrantStatus::PlanningRevisionResubmitted)
                    ->orWhere('status_sesudah', GrantStatus::PoldaReviewingPlanning)
                    ->orWhere('status_sesudah', GrantStatus::PoldaVerifiedPlanning)
                    ->orWhere('status_sesudah', GrantStatus::PoldaRejectedPlanning)
                    ->orWhere('status_sesudah', GrantStatus::PoldaRequestedPlanningRevision);
            })
            ->with(['donor', 'statusHistory', 'orgUnit'])
            ->orderByDesc('created_at')
            ->get();
    }
}
