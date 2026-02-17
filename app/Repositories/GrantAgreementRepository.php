<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Enums\ProposalChapter;
use App\Models\Grant;
use App\Models\GrantNumbering;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class GrantAgreementRepository
{
    private ?HtmlSanitizer $sanitizer = null;

    private function sanitizer(): HtmlSanitizer
    {
        return $this->sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('h1')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('a', ['href', 'target', 'rel'])
                ->allowElement('sub')
                ->allowElement('sup')
                ->allowElement('mark')
                ->allowElement('code')
        );
    }

    private function sanitizeHtml(string $html): string
    {
        return $this->sanitizer()->sanitize($html);
    }

    /** @return Collection<int, Grant> */
    public function allForUnit(OrgUnit $unit): Collection
    {
        return $unit->grants()
            ->where('tahapan', GrantStage::Agreement)
            ->with(['donor', 'statusHistory'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function findPlanningGrantByNumber(string $letterNumber, OrgUnit $unit): ?Grant
    {
        $numbering = GrantNumbering::query()
            ->where('nomor', $letterNumber)
            ->where('tahapan', GrantStage::Planning)
            ->whereHas('grant', fn ($q) => $q->where('id_satuan_kerja', $unit->id_user))
            ->first();

        return $numbering?->grant;
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    public function createAgreement(OrgUnit $unit, string $activityName, string $letterNumber, array $objectives): Grant
    {
        return DB::transaction(function () use ($unit, $activityName, $letterNumber, $objectives): Grant {
            $grant = $unit->grants()->create([
                'nama_hibah' => $activityName,
                'jenis_hibah' => GrantType::Direct->value,
                'tahapan' => GrantStage::Agreement->value,
                'ada_usulan' => false,
                'nomor_surat_dari_calon_pemberi_hibah' => $letterNumber,
            ]);

            $grant->statusHistory()->create([
                'status_sesudah' => GrantStatus::FillingReceptionData->value,
                'keterangan' => "{$unit->nama_unit} mengisi data dasar penerimaan hibah dalam rangka kegiatan {$activityName}",
            ]);

            $this->saveObjectives($grant, $objectives);

            return $grant;
        });
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    public function transitionFromPlanning(Grant $grant, array $objectives): void
    {
        DB::transaction(function () use ($grant, $objectives): void {
            $grant->update([
                'tahapan' => GrantStage::Agreement->value,
            ]);

            $grant->statusHistory()->create([
                'status_sebelum' => GrantStatus::PlanningNumberIssued->value,
                'status_sesudah' => GrantStatus::FillingReceptionData->value,
                'keterangan' => "{$grant->orgUnit->nama_unit} mengisi data dasar penerimaan hibah dalam rangka kegiatan {$grant->nama_hibah}",
            ]);

            $this->saveObjectives($grant, $objectives);
            $this->copyPlanningDataToAgreement($grant);
        });
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    public function updateReceptionBasis(Grant $grant, string $activityName, string $letterNumber, array $objectives): void
    {
        DB::transaction(function () use ($grant, $activityName, $letterNumber, $objectives): void {
            $grant->update([
                'nama_hibah' => $activityName,
                'nomor_surat_dari_calon_pemberi_hibah' => $letterNumber,
            ]);

            // Delete existing objectives for agreement stage and re-save
            $grant->information()
                ->where('tahapan', GrantStage::Agreement)
                ->where('judul', ProposalChapter::Objective->value)
                ->each(function ($info): void {
                    $info->contents()->forceDelete();
                    $info->forceDelete();
                });

            $this->saveObjectives($grant, $objectives);
        });
    }

    /**
     * @param  array<int, array{purpose: string, detail: string}>  $objectives
     */
    private function saveObjectives(Grant $grant, array $objectives): void
    {
        $info = $grant->information()->create([
            'judul' => ProposalChapter::Objective->value,
            'tahapan' => GrantStage::Agreement->value,
        ]);

        foreach ($objectives as $index => $objective) {
            $info->contents()->create([
                'subjudul' => $objective['purpose'],
                'isi' => $this->sanitizeHtml($objective['detail']),
                'nomor_urut' => $index + 1,
            ]);
        }
    }

    private function copyPlanningDataToAgreement(Grant $grant): void
    {
        $chaptersToCopy = [
            ProposalChapter::Purpose,
            ProposalChapter::Target,
            ProposalChapter::Benefit,
            ProposalChapter::ImplementationPlan,
            ProposalChapter::ReportingPlan,
            ProposalChapter::EvaluationPlan,
        ];

        foreach ($chaptersToCopy as $chapter) {
            $planningInfo = $grant->information()
                ->where('tahapan', GrantStage::Planning)
                ->where('judul', $chapter->value)
                ->first();

            if (! $planningInfo) {
                continue;
            }

            $agreementInfo = $grant->information()->create([
                'judul' => $planningInfo->judul,
                'tahapan' => GrantStage::Agreement->value,
            ]);

            $agreementInfo->contents()->createMany(
                $planningInfo->contents->map(fn ($c) => [
                    'subjudul' => $c->subjudul,
                    'isi' => $c->isi,
                    'nomor_urut' => $c->nomor_urut,
                ])->all()
            );
        }
    }

    public function isEditable(Grant $grant): bool
    {
        $latestStatus = $this->getLatestStatus($grant);

        return $latestStatus !== null && $latestStatus->isEditableBySatkerAgreement();
    }

    public function getLatestStatus(Grant $grant): ?GrantStatus
    {
        $latestHistory = $grant->statusHistory()
            ->latest('id')
            ->first();

        return $latestHistory?->status_sesudah;
    }
}
