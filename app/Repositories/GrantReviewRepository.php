<?php

namespace App\Repositories;

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\LogAction;
use App\Models\Grant;
use App\Models\GrantAssessment;
use App\Models\GrantAssessmentResult;
use App\Models\OrgUnit;
use App\Notifications\PlanningRejectedNotification;
use App\Notifications\PlanningRevisionRequestedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GrantReviewRepository
{
    /** @return LengthAwarePaginator<int, Grant> */
    public function allSubmittedToUnit(OrgUnit $unit): LengthAwarePaginator
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
            ->paginate(15);
    }

    public function getLatestStatus(Grant $grant): ?GrantStatus
    {
        $latestHistory = $grant->statusHistory()
            ->latest('id')
            ->first();

        return $latestHistory?->status_sesudah;
    }

    public function canStartReview(Grant $grant): bool
    {
        $latestStatus = $this->getLatestStatus($grant);

        return $latestStatus !== null && $latestStatus->canStartPoldaReview();
    }

    public function isUnderReview(Grant $grant): bool
    {
        return $this->getLatestStatus($grant) === GrantStatus::PoldaReviewingPlanning;
    }

    public function startReview(Grant $grant, OrgUnit $unit): void
    {
        DB::transaction(function () use ($grant, $unit): void {
            $previousStatus = $this->getLatestStatus($grant);

            $statusHistory = $grant->statusHistory()->create([
                'status_sebelum' => $previousStatus?->value,
                'status_sesudah' => GrantStatus::PoldaReviewingPlanning->value,
                'keterangan' => __('message.status-history.start-planning-review', ['unit' => $unit->nama_unit, 'activity' => $grant->nama_hibah]),
            ]);

            foreach (AssessmentAspect::cases() as $aspect) {
                $statusHistory->assessments()->create([
                    'judul' => $aspect->label(),
                    'aspek' => $aspect->value,
                    'tahapan' => GrantStage::Planning->value,
                ]);
            }

            auth()->user()?->activityLogs()->create([
                'action' => LogAction::Review,
                'message' => __('message.activity-log.start-planning-review', ['activity' => $grant->nama_hibah]),
                'metadata' => ['model_type' => Grant::class, 'model_id' => $grant->id],
            ]);
        });
    }

    public function submitAspectResult(GrantAssessment $assessment, OrgUnit $unit, AssessmentResult $result, ?string $remarks): void
    {
        DB::transaction(function () use ($assessment, $unit, $result, $remarks): void {
            $resultModel = new GrantAssessmentResult([
                'rekomendasi' => $result->value,
                'keterangan' => $remarks,
            ]);
            $resultModel->assessment()->associate($assessment);
            $resultModel->orgUnit()->associate($unit);
            $resultModel->save();

            $this->resolveIfComplete($assessment);
        });
    }

    /** @return Collection<int, GrantAssessment> */
    public function getReviewAssessments(Grant $grant): Collection
    {
        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingPlanning)
            ->latest('id')
            ->first();

        if (! $reviewingHistory) {
            return new Collection;
        }

        return $reviewingHistory->assessments()
            ->with(['contents', 'result'])
            ->get();
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

    public function findAssessmentForGrant(int $assessmentId, Grant $grant): GrantAssessment
    {
        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingPlanning)
            ->latest('id')
            ->first();

        return GrantAssessment::query()
            ->where('id', $assessmentId)
            ->where('id_riwayat_perubahan_status_hibah', $reviewingHistory->id)
            ->with('result')
            ->firstOrFail();
    }

    private function resolveIfComplete(GrantAssessment $assessment): void
    {
        $statusHistory = $assessment->statusHistory;
        $grant = $statusHistory->grant;

        $assessments = $statusHistory->assessments()->with('result')->get();

        $allHaveResults = $assessments->every(fn (GrantAssessment $a) => $a->result !== null);

        if (! $allHaveResults) {
            return;
        }

        $results = $assessments->map(fn (GrantAssessment $a) => $a->result->rekomendasi);

        if ($results->contains(AssessmentResult::Rejected)) {
            $newStatus = GrantStatus::PoldaRejectedPlanning;
            $keterangan = __('message.status-history.planning-rejected', ['activity' => $grant->nama_hibah, 'reviewer' => 'Polda']);
        } elseif ($results->contains(AssessmentResult::Revision)) {
            $newStatus = GrantStatus::PoldaRequestedPlanningRevision;
            $keterangan = __('message.status-history.planning-revision-requested', ['activity' => $grant->nama_hibah, 'reviewer' => 'Polda']);
        } else {
            $newStatus = GrantStatus::PoldaVerifiedPlanning;
            $keterangan = __('message.status-history.planning-verified', ['activity' => $grant->nama_hibah, 'reviewer' => 'Polda']);
        }

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PoldaReviewingPlanning->value,
            'status_sesudah' => $newStatus->value,
            'keterangan' => $keterangan,
        ]);

        $logAction = match ($newStatus) {
            GrantStatus::PoldaVerifiedPlanning => LogAction::Verify,
            GrantStatus::PoldaRejectedPlanning => LogAction::Reject,
            GrantStatus::PoldaRequestedPlanningRevision => LogAction::RequestRevision,
        };

        auth()->user()?->activityLogs()->create([
            'action' => $logAction,
            'message' => __('message.activity-log.resolve-planning', ['action' => $logAction->label(), 'activity' => $grant->nama_hibah]),
            'metadata' => ['model_type' => Grant::class, 'model_id' => $grant->id],
        ]);

        if ($newStatus === GrantStatus::PoldaRejectedPlanning) {
            $grant->orgUnit->user->notify(new PlanningRejectedNotification($grant, 'Polda'));
        }

        if ($newStatus === GrantStatus::PoldaRequestedPlanningRevision) {
            $grant->orgUnit->user->notify(new PlanningRevisionRequestedNotification($grant, 'Polda'));
        }
    }
}
