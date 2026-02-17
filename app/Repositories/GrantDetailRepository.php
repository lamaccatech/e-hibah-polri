<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\UnitLevel;
use App\Models\Grant;
use App\Models\GrantAssessment;
use App\Models\GrantAssessmentResult;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Collection;

class GrantDetailRepository
{
    public function findWithDetails(int $grantId): Grant
    {
        return Grant::query()
            ->with([
                'orgUnit.parent',
                'donor',
                'statusHistory' => fn ($q) => $q->orderBy('id'),
                'numberings',
            ])
            ->findOrFail($grantId);
    }

    public function canView(Grant $grant, OrgUnit $unit): bool
    {
        return match ($unit->level_unit) {
            UnitLevel::SatuanKerja => $grant->id_satuan_kerja === $unit->id_user,
            UnitLevel::SatuanInduk => $unit->children()->where('id_user', $grant->id_satuan_kerja)->exists(),
            UnitLevel::Mabes => true,
        };
    }

    /**
     * @return Collection<int, \App\Models\GrantInformation>
     */
    public function getProposalChapters(Grant $grant): Collection
    {
        return $grant->information()
            ->where('tahapan', GrantStage::Planning)
            ->with(['contents' => fn ($q) => $q->orderBy('nomor_urut')])
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\GrantBudgetPlan>
     */
    public function getBudgetPlans(Grant $grant): Collection
    {
        return $grant->budgetPlans()
            ->orderBy('nomor_urut')
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\GrantWithdrawalPlan>
     */
    public function getWithdrawalPlans(Grant $grant): Collection
    {
        return $grant->withdrawalPlans()
            ->orderBy('nomor_urut')
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\ActivitySchedule>
     */
    public function getActivitySchedules(Grant $grant): Collection
    {
        return $grant->activitySchedules()->get();
    }

    /**
     * Get the Satker's original assessment contents, keyed by aspect value.
     *
     * @return array<string, Collection<int, \App\Models\GrantAssessmentContent>>
     */
    public function getSatkerAssessments(Grant $grant): array
    {
        $assessmentHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::CreatingPlanningAssessment)
            ->latest('id')
            ->first();

        if (! $assessmentHistory) {
            return [];
        }

        return $assessmentHistory->assessments()
            ->with('contents')
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->contents)
            ->all();
    }

    /**
     * Get Polda's assessment results, keyed by aspect value.
     *
     * @return array<string, GrantAssessmentResult>
     */
    public function getPoldaAssessmentResults(Grant $grant): array
    {
        $poldaReviewHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingPlanning)
            ->latest('id')
            ->first();

        if (! $poldaReviewHistory) {
            return [];
        }

        return $poldaReviewHistory->assessments()
            ->with(['result.orgUnit'])
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->result)
            ->filter()
            ->all();
    }

    /**
     * Get Mabes's assessment results, keyed by aspect value.
     *
     * @return array<string, GrantAssessmentResult>
     */
    public function getMabesAssessmentResults(Grant $grant): array
    {
        $mabesReviewHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::MabesReviewingPlanning)
            ->latest('id')
            ->first();

        if (! $mabesReviewHistory) {
            return [];
        }

        return $mabesReviewHistory->assessments()
            ->with(['result.orgUnit'])
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->result)
            ->filter()
            ->all();
    }

    /**
     * @return Collection<int, \App\Models\GrantInformation>
     */
    public function getAgreementChapters(Grant $grant): Collection
    {
        return $grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->with(['contents' => fn ($q) => $q->orderBy('nomor_urut')])
            ->get();
    }

    /**
     * Get the Satker's agreement assessment contents, keyed by aspect value.
     *
     * @return array<string, Collection<int, \App\Models\GrantAssessmentContent>>
     */
    public function getSatkerAgreementAssessments(Grant $grant): array
    {
        $assessmentHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::CreatingAgreementAssessment)
            ->latest('id')
            ->first();

        if (! $assessmentHistory) {
            return [];
        }

        return $assessmentHistory->assessments()
            ->with('contents')
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->contents)
            ->all();
    }

    /**
     * Get Polda's agreement assessment results, keyed by aspect value.
     *
     * @return array<string, GrantAssessmentResult>
     */
    public function getPoldaAgreementAssessmentResults(Grant $grant): array
    {
        $poldaReviewHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingAgreement)
            ->latest('id')
            ->first();

        if (! $poldaReviewHistory) {
            return [];
        }

        return $poldaReviewHistory->assessments()
            ->with(['result.orgUnit'])
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->result)
            ->filter()
            ->all();
    }

    /**
     * Get Mabes's agreement assessment results, keyed by aspect value.
     *
     * @return array<string, GrantAssessmentResult>
     */
    public function getMabesAgreementAssessmentResults(Grant $grant): array
    {
        $mabesReviewHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::MabesReviewingAgreement)
            ->latest('id')
            ->first();

        if (! $mabesReviewHistory) {
            return [];
        }

        return $mabesReviewHistory->assessments()
            ->with(['result.orgUnit'])
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->result)
            ->filter()
            ->all();
    }
}
