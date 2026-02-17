<?php

namespace App\Livewire\GrantAgreement;

use App\Enums\GrantStage;
use App\Enums\ProposalChapter;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Livewire\Component;

class AdditionalMaterials extends Component
{
    public Grant $grant;

    /** @var array<string, array<int, string>> Keyed by ProposalChapter value */
    public array $chapters = [];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->isEditable($grant), 403);

        // Only direct grants (ada_usulan=false) use this step
        abort_if($grant->ada_usulan, 404);

        $this->grant = $grant;
        $this->initializeChapters();
        $this->loadExistingData();
    }

    protected function rules(): array
    {
        $rules = [];

        foreach ($this->additionalChapters() as $chapter) {
            $prompts = $chapter->prompts();
            foreach ($prompts as $i => $prompt) {
                $rules["chapters.{$chapter->value}.{$i}"] = ['required', 'string', 'min:10'];
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach ($this->additionalChapters() as $chapter) {
            foreach ($chapter->prompts() as $i => $prompt) {
                $attributes["chapters.{$chapter->value}.{$i}"] = $chapter->label();
            }
        }

        return $attributes;
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        // Transform chapters: pair each paragraph with its prompt as subjudul
        $chaptersData = [];
        foreach ($this->additionalChapters() as $chapter) {
            $prompts = $chapter->prompts();
            $chaptersData[$chapter->value] = array_map(
                fn ($content, $i) => ['subjudul' => $prompts[$i] ?? '', 'isi' => $content],
                $this->chapters[$chapter->value],
                array_keys($this->chapters[$chapter->value]),
            );
        }

        $repository->saveAdditionalMaterials($this->grant, $chaptersData);

        $this->redirect(route('grant-agreement.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.grant-agreement.additional-materials', [
            'chapterCases' => $this->additionalChapters(),
        ]);
    }

    /** @return ProposalChapter[] */
    private function additionalChapters(): array
    {
        return [
            ProposalChapter::Target,
            ProposalChapter::Benefit,
            ProposalChapter::ImplementationPlan,
            ProposalChapter::ReportingPlan,
            ProposalChapter::EvaluationPlan,
        ];
    }

    private function initializeChapters(): void
    {
        foreach ($this->additionalChapters() as $chapter) {
            $prompts = $chapter->prompts();
            $paragraphCount = max(count($prompts), 1);
            $this->chapters[$chapter->value] = array_fill(0, $paragraphCount, '');
        }
    }

    private function loadExistingData(): void
    {
        $existingInfo = $this->grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->whereIn('judul', array_map(fn ($c) => $c->value, $this->additionalChapters()))
            ->with('contents')
            ->get();

        foreach ($existingInfo as $info) {
            $contents = $info->contents->sortBy('nomor_urut')->pluck('isi')->values()->all();
            if (! empty($contents)) {
                $this->chapters[$info->judul] = $contents;
            }
        }
    }
}
