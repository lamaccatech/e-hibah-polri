<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Models\GrantBudgetPlan;
use Illuminate\Support\Facades\DB;

class MabesDashboardRepository
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
    public function getCounts(): array
    {
        return [
            'planningCreated' => $this->countPlanningCreated(),
            'planningUnprocessed' => $this->countByLatestStatus(GrantStage::Planning, [
                GrantStatus::PoldaVerifiedPlanning,
            ]),
            'planningProcessing' => $this->countByLatestStatus(GrantStage::Planning, [
                GrantStatus::MabesReviewingPlanning,
            ]),
            'planningRejected' => $this->countByLatestStatus(GrantStage::Planning, [
                GrantStatus::MabesRejectedPlanning,
            ]),
            'agreementCreated' => $this->countAgreementCreated(),
            'agreementUnprocessed' => $this->countByLatestStatus(GrantStage::Agreement, [
                GrantStatus::PoldaVerifiedAgreement,
            ]),
            'agreementProcessing' => $this->countByLatestStatus(GrantStage::Agreement, [
                GrantStatus::MabesReviewingAgreement,
            ]),
            'agreementRejected' => $this->countByLatestStatus(GrantStage::Agreement, [
                GrantStatus::MabesRejectedAgreement,
            ]),
        ];
    }

    /**
     * @return array{
     *     goodsServices: array{plan: float, realization: float},
     *     money: array{plan: float, realization: float},
     * }
     */
    public function getRealization(): array
    {
        return [
            'goodsServices' => [
                'plan' => $this->sumBudgetByFormAndScope(['%BARANG%', '%JASA%'], planned: true),
                'realization' => $this->sumBudgetByFormAndScope(['%BARANG%', '%JASA%'], planned: false),
            ],
            'money' => [
                'plan' => $this->sumBudgetByFormAndScope(['%UANG%'], planned: true),
                'realization' => $this->sumBudgetByFormAndScope(['%UANG%'], planned: false),
            ],
        ];
    }

    /**
     * @return list<array{year: string, plan: float, realization: float}>
     */
    public function getYearlyTrend(): array
    {
        $planStatuses = $this->planningSubmittedOrBeyondStatuses();
        $realizationStatus = GrantStatus::UploadingSignedAgreement->value;

        $rows = GrantBudgetPlan::query()
            ->join('hibah', 'hibah.id', '=', 'rencana_anggaran_biaya_hibah.id_hibah')
            ->whereNull('hibah.deleted_at')
            ->whereNull('rencana_anggaran_biaya_hibah.deleted_at')
            ->whereHas('grant.statusHistory', function ($query) use ($planStatuses): void {
                $query->whereIn('status_sesudah', $planStatuses);
            })
            ->select(
                DB::raw('EXTRACT(YEAR FROM hibah.created_at)::int as year'),
                DB::raw('SUM(rencana_anggaran_biaya_hibah.nilai) as plan'),
                DB::raw("SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM riwayat_perubahan_status_hibah
                    WHERE riwayat_perubahan_status_hibah.id_hibah = hibah.id
                    AND riwayat_perubahan_status_hibah.status_sesudah = '{$realizationStatus}'
                ) THEN rencana_anggaran_biaya_hibah.nilai ELSE 0 END) as realization"),
            )
            ->groupBy(DB::raw('EXTRACT(YEAR FROM hibah.created_at)'))
            ->orderBy('year')
            ->get();

        return $rows->map(fn ($row) => [
            'year' => (string) $row->year,
            'plan' => (float) $row->plan,
            'realization' => (float) $row->realization,
        ])->all();
    }

    /**
     * Planning grants that have ever reached Polda verification or beyond (Mabes-level).
     */
    private function countPlanningCreated(): int
    {
        $submissionStatuses = [
            GrantStatus::PoldaVerifiedPlanning->value,
            GrantStatus::MabesReviewingPlanning->value,
            GrantStatus::MabesVerifiedPlanning->value,
            GrantStatus::MabesRejectedPlanning->value,
            GrantStatus::MabesRequestedPlanningRevision->value,
            GrantStatus::PlanningNumberIssued->value,
        ];

        return Grant::query()
            ->where('tahapan', GrantStage::Planning)
            ->whereHas('statusHistory', function ($query) use ($submissionStatuses): void {
                $query->whereIn('status_sesudah', $submissionStatuses);
            })
            ->count();
    }

    /**
     * Agreement grants that have ever reached Polda verification or beyond (Mabes-level).
     */
    private function countAgreementCreated(): int
    {
        $submissionStatuses = [
            GrantStatus::PoldaVerifiedAgreement->value,
            GrantStatus::MabesReviewingAgreement->value,
            GrantStatus::MabesVerifiedAgreement->value,
            GrantStatus::MabesRejectedAgreement->value,
            GrantStatus::MabesRequestedAgreementRevision->value,
            GrantStatus::AgreementNumberIssued->value,
            GrantStatus::UploadingSignedAgreement->value,
            GrantStatus::SubmittingToFinanceMinistry->value,
        ];

        return Grant::query()
            ->where('tahapan', GrantStage::Agreement)
            ->whereHas('statusHistory', function ($query) use ($submissionStatuses): void {
                $query->whereIn('status_sesudah', $submissionStatuses);
            })
            ->count();
    }

    /**
     * Count grants whose latest status matches the given statuses.
     *
     * @param  list<GrantStatus>  $statuses
     */
    private function countByLatestStatus(GrantStage $stage, array $statuses): int
    {
        $statusValues = array_map(fn (GrantStatus $s) => $s->value, $statuses);

        return Grant::query()
            ->where('tahapan', $stage)
            ->whereIn(
                DB::raw($this->latestStatusSubquery()),
                $statusValues
            )
            ->count();
    }

    /**
     * Sum budget plan values filtered by grant form type and scope (plan vs realization).
     *
     * @param  list<string>  $formPatterns  LIKE patterns for bentuk_hibah
     */
    private function sumBudgetByFormAndScope(array $formPatterns, bool $planned): float
    {
        $statuses = $planned
            ? $this->planningSubmittedOrBeyondStatuses()
            : [GrantStatus::UploadingSignedAgreement->value];

        $query = GrantBudgetPlan::query()
            ->join('hibah', 'hibah.id', '=', 'rencana_anggaran_biaya_hibah.id_hibah')
            ->whereNull('hibah.deleted_at')
            ->whereNull('rencana_anggaran_biaya_hibah.deleted_at')
            ->whereHas('grant.statusHistory', function ($q) use ($statuses): void {
                $q->whereIn('status_sesudah', $statuses);
            })
            ->where(function ($q) use ($formPatterns): void {
                foreach ($formPatterns as $pattern) {
                    $q->orWhere('hibah.bentuk_hibah', 'LIKE', $pattern);
                }
            });

        return (float) $query->sum('rencana_anggaran_biaya_hibah.nilai');
    }

    /**
     * Statuses that indicate a grant has been submitted to Polda or beyond.
     *
     * @return list<string>
     */
    private function planningSubmittedOrBeyondStatuses(): array
    {
        return [
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
    }

    /**
     * Correlated subquery to get the latest status for a grant.
     */
    private function latestStatusSubquery(): string
    {
        return '(SELECT status_sesudah FROM riwayat_perubahan_status_hibah WHERE id_hibah = hibah.id ORDER BY id DESC LIMIT 1)';
    }
}
