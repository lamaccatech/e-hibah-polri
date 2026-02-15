<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class GrantPlanningRepository
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
            ->where('tahapan', GrantStage::Planning)
            ->with('donor')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findForUnit(int $grantId, OrgUnit $unit): Grant
    {
        return $unit->grants()->findOrFail($grantId);
    }

    public function createGrant(OrgUnit $unit, string $activityName): Grant
    {
        $grant = $unit->grants()->create([
            'nama_hibah' => $activityName,
            'jenis_hibah' => GrantType::Planned->value,
            'tahapan' => GrantStage::Planning->value,
        ]);

        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
        ]);

        return $grant;
    }

    /** @return Collection<int, Donor> */
    public function allDonors(): Collection
    {
        return Donor::query()->orderBy('nama')->get();
    }

    /**
     * @param  array{nama: string, asal: string, alamat: string, negara: string, kategori: string}  $data
     */
    public function createDonor(array $data): Donor
    {
        return Donor::create($data);
    }

    public function linkDonor(Grant $grant, int $donorId): void
    {
        $grant->update(['id_pemberi_hibah' => $donorId]);

        $grant->statusHistory()->create([
            'status_sebelum' => $this->getLatestStatus($grant)?->value,
            'status_sesudah' => GrantStatus::FillingDonorCandidate->value,
        ]);
    }

    /**
     * @param  array<string, array<int, string>>  $chapters  Keyed by ProposalChapter value
     * @param  array<int, array{uraian: string, volume: string, satuan: string, harga_satuan: string}>  $budgetItems
     * @param  array<int, array{uraian_kegiatan: string, tanggal_mulai: string, tanggal_selesai: string}>  $schedules
     */
    public function saveProposalDocument(Grant $grant, array $chapters, array $budgetItems, array $schedules, string $currency): void
    {
        DB::transaction(function () use ($grant, $chapters, $budgetItems, $schedules, $currency): void {
            // Delete existing proposal data for re-save
            $grant->information()
                ->where('tahapan', GrantStage::Planning)
                ->forceDelete();
            $grant->budgetPlans()->forceDelete();
            $grant->activitySchedules()->forceDelete();

            // Create chapters with contents
            foreach ($chapters as $chapterValue => $paragraphs) {
                $info = $grant->information()->create([
                    'judul' => $chapterValue,
                    'tahapan' => GrantStage::Planning->value,
                ]);

                foreach ($paragraphs as $index => $content) {
                    if (trim($content) === '') {
                        continue;
                    }

                    $info->contents()->create([
                        'subjudul' => '',
                        'isi' => $this->sanitizeHtml($content),
                        'nomor_urut' => $index + 1,
                    ]);
                }
            }

            // Create budget items and calculate total value
            $totalValue = '0';
            foreach ($budgetItems as $item) {
                $grant->budgetPlans()->create([
                    'uraian' => $item['uraian'],
                    'volume' => $item['volume'],
                    'satuan' => $item['satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                ]);

                $totalValue = bcadd($totalValue, bcmul($item['volume'], $item['harga_satuan'], 2), 2);
            }

            // Create schedules
            foreach ($schedules as $schedule) {
                $grant->activitySchedules()->create([
                    'uraian_kegiatan' => $schedule['uraian_kegiatan'],
                    'tanggal_mulai' => $schedule['tanggal_mulai'],
                    'tanggal_selesai' => $schedule['tanggal_selesai'],
                ]);
            }

            // Update grant value and currency
            $grant->update([
                'nilai_hibah' => $totalValue,
                'mata_uang' => $currency,
            ]);

            $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::CreatingProposalDocument->value,
            ]);
        });
    }

    /**
     * @param  array<string, array{judul: string, aspek: ?string, paragraphs: array<int, string>}>  $aspects
     */
    public function saveAssessment(Grant $grant, array $aspects): void
    {
        DB::transaction(function () use ($grant, $aspects): void {
            // Get latest status history for linking assessments
            $latestHistory = $grant->statusHistory()->latest('id')->first();

            // Delete existing assessments for this grant's planning stage
            if ($latestHistory) {
                $grant->statusHistory()
                    ->get()
                    ->each(function ($history): void {
                        $history->assessments()
                            ->where('tahapan', GrantStage::Planning)
                            ->each(function ($assessment): void {
                                $assessment->contents()->forceDelete();
                                $assessment->forceDelete();
                            });
                    });
            }

            // Create new status history for assessment
            $statusHistory = $grant->statusHistory()->create([
                'status_sebelum' => $this->getLatestStatus($grant)?->value,
                'status_sesudah' => GrantStatus::CreatingPlanningAssessment->value,
            ]);

            // Create assessments with contents
            foreach ($aspects as $aspect) {
                $assessment = $statusHistory->assessments()->create([
                    'judul' => $aspect['judul'],
                    'aspek' => $aspect['aspek'],
                    'tahapan' => GrantStage::Planning->value,
                ]);

                foreach ($aspect['paragraphs'] as $index => $content) {
                    if (trim($content) === '') {
                        continue;
                    }

                    $assessment->contents()->create([
                        'subjudul' => '',
                        'isi' => $this->sanitizeHtml($content),
                        'nomor_urut' => $index + 1,
                    ]);
                }
            }
        });
    }

    public function submitToPolda(Grant $grant): void
    {
        $grant->statusHistory()->create([
            'status_sebelum' => $this->getLatestStatus($grant)?->value,
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
        ]);
    }

    public function canSubmit(Grant $grant): bool
    {
        $hasDonor = $grant->id_pemberi_hibah !== null;

        $hasChapters = $grant->information()
            ->where('tahapan', GrantStage::Planning)
            ->exists();

        $hasAssessment = $grant->statusHistory()
            ->whereHas('assessments', fn ($q) => $q->where('tahapan', GrantStage::Planning))
            ->exists();

        return $hasDonor && $hasChapters && $hasAssessment;
    }

    public function getLatestStatus(Grant $grant): ?GrantStatus
    {
        $latestHistory = $grant->statusHistory()
            ->latest('id')
            ->first();

        return $latestHistory?->status_sesudah;
    }
}
