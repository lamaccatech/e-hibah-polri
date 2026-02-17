<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Models\OrgUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PoldaDashboardRepository
{
    /**
     * @return array{
     *     planningCreated: int,
     *     planningUnprocessed: int,
     *     planningProcessing: int,
     *     planningRejected: int,
     *     agreementCreated: int,
     *     agreementUnprocessed: int,
     *     agreementProcessing: int,
     *     agreementRejected: int,
     * }
     */
    public function getCounts(OrgUnit $unit): array
    {
        $childIds = $this->childUnitUserIds($unit);

        return [
            'planningCreated' => $this->countPlanningCreated($childIds),
            'planningUnprocessed' => $this->countByLatestStatus($childIds, GrantStage::Planning, [
                GrantStatus::PlanningSubmittedToPolda,
                GrantStatus::PlanningRevisionResubmitted,
            ]),
            'planningProcessing' => $this->countByLatestStatus($childIds, GrantStage::Planning, [
                GrantStatus::PoldaReviewingPlanning,
            ]),
            'planningRejected' => $this->countByLatestStatus($childIds, GrantStage::Planning, [
                GrantStatus::PoldaRejectedPlanning,
            ]),
            'agreementCreated' => $this->countAgreementCreated($childIds),
            'agreementUnprocessed' => $this->countByLatestStatus($childIds, GrantStage::Agreement, [
                GrantStatus::AgreementSubmittedToPolda,
                GrantStatus::AgreementRevisionResubmitted,
            ]),
            'agreementProcessing' => $this->countByLatestStatus($childIds, GrantStage::Agreement, [
                GrantStatus::PoldaReviewingAgreement,
            ]),
            'agreementRejected' => $this->countByLatestStatus($childIds, GrantStage::Agreement, [
                GrantStatus::PoldaRejectedAgreement,
            ]),
        ];
    }

    /** @return LengthAwarePaginator<int, Grant> */
    public function getInbox(OrgUnit $unit, int $perPage = 10): LengthAwarePaginator
    {
        $childIds = $this->childUnitUserIds($unit);

        $planningStatuses = [
            GrantStatus::PlanningSubmittedToPolda->value,
            GrantStatus::PlanningRevisionResubmitted->value,
        ];

        $agreementStatuses = [
            GrantStatus::AgreementSubmittedToPolda->value,
            GrantStatus::AgreementRevisionResubmitted->value,
        ];

        $allStatuses = array_merge($planningStatuses, $agreementStatuses);

        return Grant::query()
            ->whereIn('id_satuan_kerja', $childIds)
            ->whereIn(
                DB::raw($this->latestStatusSubquery()),
                $allStatuses
            )
            ->with(['orgUnit'])
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    /** @return Collection<int, int> */
    private function childUnitUserIds(OrgUnit $unit): Collection
    {
        return $unit->children()->pluck('id_user');
    }

    /**
     * Planning grants that have ever reached submission or beyond.
     */
    private function countPlanningCreated(Collection $childIds): int
    {
        $submissionStatuses = [
            GrantStatus::PlanningSubmittedToPolda->value,
            GrantStatus::PlanningRevisionResubmitted->value,
            GrantStatus::PoldaReviewingPlanning->value,
            GrantStatus::PoldaVerifiedPlanning->value,
            GrantStatus::PoldaRejectedPlanning->value,
            GrantStatus::PoldaRequestedPlanningRevision->value,
            GrantStatus::MabesReviewingPlanning->value,
            GrantStatus::MabesVerifiedPlanning->value,
            GrantStatus::MabesRejectedPlanning->value,
            GrantStatus::MabesRequestedPlanningRevision->value,
            GrantStatus::PlanningNumberIssued->value,
        ];

        return Grant::query()
            ->whereIn('id_satuan_kerja', $childIds)
            ->where('tahapan', GrantStage::Planning)
            ->whereHas('statusHistory', function ($query) use ($submissionStatuses): void {
                $query->whereIn('status_sesudah', $submissionStatuses);
            })
            ->count();
    }

    /**
     * Agreement grants that have ever reached submission or beyond.
     */
    private function countAgreementCreated(Collection $childIds): int
    {
        $submissionStatuses = [
            GrantStatus::AgreementSubmittedToPolda->value,
            GrantStatus::AgreementRevisionResubmitted->value,
            GrantStatus::PoldaReviewingAgreement->value,
            GrantStatus::PoldaVerifiedAgreement->value,
            GrantStatus::PoldaRejectedAgreement->value,
            GrantStatus::PoldaRequestedAgreementRevision->value,
            GrantStatus::MabesReviewingAgreement->value,
            GrantStatus::MabesVerifiedAgreement->value,
            GrantStatus::MabesRejectedAgreement->value,
            GrantStatus::MabesRequestedAgreementRevision->value,
            GrantStatus::AgreementNumberIssued->value,
            GrantStatus::UploadingSignedAgreement->value,
            GrantStatus::SubmittingToFinanceMinistry->value,
        ];

        return Grant::query()
            ->whereIn('id_satuan_kerja', $childIds)
            ->where('tahapan', GrantStage::Agreement)
            ->whereHas('statusHistory', function ($query) use ($submissionStatuses): void {
                $query->whereIn('status_sesudah', $submissionStatuses);
            })
            ->count();
    }

    /**
     * Count grants whose latest status matches the given statuses.
     *
     * @param  Collection<int, int>  $childIds
     * @param  list<GrantStatus>  $statuses
     */
    private function countByLatestStatus(Collection $childIds, GrantStage $stage, array $statuses): int
    {
        $statusValues = array_map(fn (GrantStatus $s) => $s->value, $statuses);

        return Grant::query()
            ->whereIn('id_satuan_kerja', $childIds)
            ->where('tahapan', $stage)
            ->whereIn(
                DB::raw($this->latestStatusSubquery()),
                $statusValues
            )
            ->count();
    }

    /**
     * Correlated subquery to get the latest status for a grant.
     */
    private function latestStatusSubquery(): string
    {
        return '(SELECT status_sesudah FROM riwayat_perubahan_status_hibah WHERE id_hibah = hibah.id ORDER BY id DESC LIMIT 1)';
    }
}
