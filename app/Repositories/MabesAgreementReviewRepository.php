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
use App\Notifications\AgreementNumberIssuedNotification;
use App\Notifications\AgreementRejectedNotification;
use App\Notifications\AgreementRevisionRequestedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MabesAgreementReviewRepository
{
    /** @return LengthAwarePaginator<int, Grant> */
    public function allPoldaVerifiedAgreements(): LengthAwarePaginator
    {
        return Grant::query()
            ->where('tahapan', GrantStage::Agreement)
            ->whereHas('statusHistory', function ($query): void {
                $query->where('status_sesudah', GrantStatus::PoldaVerifiedAgreement)
                    ->orWhere('status_sesudah', GrantStatus::MabesReviewingAgreement)
                    ->orWhere('status_sesudah', GrantStatus::MabesVerifiedAgreement)
                    ->orWhere('status_sesudah', GrantStatus::MabesRejectedAgreement)
                    ->orWhere('status_sesudah', GrantStatus::MabesRequestedAgreementRevision)
                    ->orWhere('status_sesudah', GrantStatus::AgreementNumberIssued);
            })
            ->with(['donor', 'statusHistory', 'orgUnit.parent', 'tags'])
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

        return $latestStatus !== null && $latestStatus->canStartMabesAgreementReview();
    }

    public function isUnderReview(Grant $grant): bool
    {
        return $this->getLatestStatus($grant) === GrantStatus::MabesReviewingAgreement;
    }

    public function startReview(Grant $grant, OrgUnit $unit): void
    {
        DB::transaction(function () use ($grant, $unit): void {
            $previousStatus = $this->getLatestStatus($grant);

            $statusHistory = $grant->statusHistory()->create([
                'status_sebelum' => $previousStatus?->value,
                'status_sesudah' => GrantStatus::MabesReviewingAgreement->value,
                'keterangan' => __('message.status-history.start-agreement-review', ['unit' => $unit->nama_unit, 'activity' => $grant->nama_hibah]),
            ]);

            foreach (AssessmentAspect::cases() as $aspect) {
                $statusHistory->assessments()->create([
                    'judul' => $aspect->label(),
                    'aspek' => $aspect->value,
                    'tahapan' => GrantStage::Agreement->value,
                ]);
            }

            auth()->user()?->activityLogs()->create([
                'action' => LogAction::Review,
                'message' => __('message.activity-log.start-agreement-review', ['activity' => $grant->nama_hibah]),
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
            ->where('status_sesudah', GrantStatus::MabesReviewingAgreement)
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
     * Get Polda's assessment results, keyed by aspect value.
     *
     * @return array<string, GrantAssessmentResult>
     */
    public function getPoldaAssessments(Grant $grant): array
    {
        $poldaReviewHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingAgreement)
            ->latest('id')
            ->first();

        if (! $poldaReviewHistory) {
            return [];
        }

        return $poldaReviewHistory->assessments()
            ->with('result')
            ->get()
            ->keyBy(fn (GrantAssessment $a) => $a->aspek->value)
            ->map(fn (GrantAssessment $a) => $a->result)
            ->filter()
            ->all();
    }

    public function findAssessmentForGrant(int $assessmentId, Grant $grant): GrantAssessment
    {
        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::MabesReviewingAgreement)
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
            $newStatus = GrantStatus::MabesRejectedAgreement;
            $keterangan = __('message.status-history.agreement-rejected', ['activity' => $grant->nama_hibah, 'reviewer' => 'Mabes']);
        } elseif ($results->contains(AssessmentResult::Revision)) {
            $newStatus = GrantStatus::MabesRequestedAgreementRevision;
            $keterangan = __('message.status-history.agreement-revision-requested', ['activity' => $grant->nama_hibah, 'reviewer' => 'Mabes']);
        } else {
            $newStatus = GrantStatus::MabesVerifiedAgreement;
            $keterangan = __('message.status-history.agreement-verified', ['activity' => $grant->nama_hibah, 'reviewer' => 'Mabes']);
        }

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::MabesReviewingAgreement->value,
            'status_sesudah' => $newStatus->value,
            'keterangan' => $keterangan,
        ]);

        $logAction = match ($newStatus) {
            GrantStatus::MabesVerifiedAgreement => LogAction::Verify,
            GrantStatus::MabesRejectedAgreement => LogAction::Reject,
            GrantStatus::MabesRequestedAgreementRevision => LogAction::RequestRevision,
        };

        auth()->user()?->activityLogs()->create([
            'action' => $logAction,
            'message' => __('message.activity-log.resolve-agreement', ['action' => $logAction->label(), 'activity' => $grant->nama_hibah]),
            'metadata' => ['model_type' => Grant::class, 'model_id' => $grant->id],
        ]);

        if ($newStatus === GrantStatus::MabesRejectedAgreement) {
            $grant->orgUnit->user->notify(new AgreementRejectedNotification($grant, 'Mabes'));
        }

        if ($newStatus === GrantStatus::MabesRequestedAgreementRevision) {
            $grant->orgUnit->user->notify(new AgreementRevisionRequestedNotification($grant, 'Mabes'));
        }

        if ($newStatus === GrantStatus::MabesVerifiedAgreement) {
            $this->issueAgreementNumber($grant);
        }
    }

    private function issueAgreementNumber(Grant $grant): void
    {
        $numberingRepository = app(GrantNumberingRepository::class);
        $numbering = $numberingRepository->issueAgreementNumber($grant);

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::MabesVerifiedAgreement->value,
            'status_sesudah' => GrantStatus::AgreementNumberIssued->value,
            'keterangan' => __('message.status-history.agreement-number-issued', ['activity' => $grant->nama_hibah]),
        ]);

        $satkerUser = $grant->orgUnit->user;
        $satkerUser->notify(new AgreementNumberIssuedNotification($grant, $numbering->nomor));
    }
}
