<?php

namespace App\Repositories;

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Models\GrantAssessment;
use App\Models\GrantAssessmentResult;
use App\Models\OrgUnit;
use App\Notifications\AgreementRejectedNotification;
use App\Notifications\AgreementRevisionRequestedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AgreementReviewRepository
{
    /** @return Collection<int, Grant> */
    public function allSubmittedToUnit(OrgUnit $unit): Collection
    {
        $childUnitUserIds = $unit->children()->pluck('id_user');

        return Grant::query()
            ->whereIn('id_satuan_kerja', $childUnitUserIds)
            ->where('tahapan', GrantStage::Agreement)
            ->whereHas('statusHistory', function ($query): void {
                $query->where('status_sesudah', GrantStatus::AgreementSubmittedToPolda)
                    ->orWhere('status_sesudah', GrantStatus::AgreementRevisionResubmitted)
                    ->orWhere('status_sesudah', GrantStatus::PoldaReviewingAgreement)
                    ->orWhere('status_sesudah', GrantStatus::PoldaVerifiedAgreement)
                    ->orWhere('status_sesudah', GrantStatus::PoldaRejectedAgreement)
                    ->orWhere('status_sesudah', GrantStatus::PoldaRequestedAgreementRevision);
            })
            ->with(['donor', 'statusHistory', 'orgUnit'])
            ->orderByDesc('created_at')
            ->get();
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

        return $latestStatus !== null && $latestStatus->canStartPoldaAgreementReview();
    }

    public function isUnderReview(Grant $grant): bool
    {
        return $this->getLatestStatus($grant) === GrantStatus::PoldaReviewingAgreement;
    }

    public function startReview(Grant $grant, OrgUnit $unit): void
    {
        DB::transaction(function () use ($grant, $unit): void {
            $previousStatus = $this->getLatestStatus($grant);

            $statusHistory = $grant->statusHistory()->create([
                'status_sebelum' => $previousStatus?->value,
                'status_sesudah' => GrantStatus::PoldaReviewingAgreement->value,
                'keterangan' => "{$unit->nama_unit} memulai kajian perjanjian hibah untuk kegiatan {$grant->nama_hibah}",
            ]);

            foreach (AssessmentAspect::cases() as $aspect) {
                $statusHistory->assessments()->create([
                    'judul' => $aspect->label(),
                    'aspek' => $aspect->value,
                    'tahapan' => GrantStage::Agreement->value,
                ]);
            }
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
            ->where('status_sesudah', GrantStatus::PoldaReviewingAgreement)
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

    public function findAssessmentForGrant(int $assessmentId, Grant $grant): GrantAssessment
    {
        $reviewingHistory = $grant->statusHistory()
            ->where('status_sesudah', GrantStatus::PoldaReviewingAgreement)
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
            $newStatus = GrantStatus::PoldaRejectedAgreement;
            $keterangan = "Perjanjian hibah untuk kegiatan {$grant->nama_hibah} ditolak oleh Polda";
        } elseif ($results->contains(AssessmentResult::Revision)) {
            $newStatus = GrantStatus::PoldaRequestedAgreementRevision;
            $keterangan = "Polda meminta revisi untuk perjanjian hibah kegiatan {$grant->nama_hibah}";
        } else {
            $newStatus = GrantStatus::PoldaVerifiedAgreement;
            $keterangan = "Perjanjian hibah untuk kegiatan {$grant->nama_hibah} disetujui oleh Polda";
        }

        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PoldaReviewingAgreement->value,
            'status_sesudah' => $newStatus->value,
            'keterangan' => $keterangan,
        ]);

        if ($newStatus === GrantStatus::PoldaRejectedAgreement) {
            $grant->orgUnit->user->notify(new AgreementRejectedNotification($grant, 'Polda'));
        }

        if ($newStatus === GrantStatus::PoldaRequestedAgreementRevision) {
            $grant->orgUnit->user->notify(new AgreementRevisionRequestedNotification($grant, 'Polda'));
        }
    }
}
