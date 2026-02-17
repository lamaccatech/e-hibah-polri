<?php

namespace App\Livewire\GrantAgreement;

use App\Enums\GrantStage;
use App\Enums\ProposalChapter;
use App\Models\Grant;
use App\Repositories\GrantAgreementRepository;
use Illuminate\View\View;
use Livewire\Component;

class OtherMaterials extends Component
{
    public Grant $grant;

    /** @var array<int, array{title: string, paragraphs: array<int, string>}> */
    public array $customChapters = [];

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantAgreementRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;
        $this->loadExistingData();
    }

    protected function rules(): array
    {
        $rules = [];

        foreach ($this->customChapters as $i => $chapter) {
            $rules["customChapters.{$i}.title"] = ['required', 'string', 'max:255'];
            foreach ($chapter['paragraphs'] as $j => $paragraph) {
                $rules["customChapters.{$i}.paragraphs.{$j}"] = ['required', 'string', 'min:10'];
            }
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'customChapters.*.title' => __('page.grant-planning-proposal.label-chapter-title'),
            'customChapters.*.paragraphs.*' => __('page.grant-planning-proposal.add-paragraph'),
        ];
    }

    public function addCustomChapter(): void
    {
        $this->customChapters[] = [
            'title' => '',
            'paragraphs' => [''],
        ];
    }

    public function removeCustomChapter(int $index): void
    {
        unset($this->customChapters[$index]);
        $this->customChapters = array_values($this->customChapters);
    }

    public function addCustomChapterParagraph(int $chapterIndex): void
    {
        $this->customChapters[$chapterIndex]['paragraphs'][] = '';
    }

    public function removeCustomChapterParagraph(int $chapterIndex, int $paragraphIndex): void
    {
        unset($this->customChapters[$chapterIndex]['paragraphs'][$paragraphIndex]);
        $this->customChapters[$chapterIndex]['paragraphs'] = array_values($this->customChapters[$chapterIndex]['paragraphs']);
    }

    public function save(GrantAgreementRepository $repository): void
    {
        $this->validate();

        $repository->saveOtherMaterials($this->grant, $this->customChapters);

        $this->redirect(route('grant-agreement.draft', $this->grant), navigate: true);
    }

    public function skip(GrantAgreementRepository $repository): void
    {
        $repository->skipOtherMaterials($this->grant);

        $this->redirect(route('grant-agreement.draft', $this->grant), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.grant-agreement.other-materials');
    }

    private function loadExistingData(): void
    {
        $knownChapterValues = array_map(fn ($c) => $c->value, ProposalChapter::cases());

        $customInfo = $this->grant->information()
            ->where('tahapan', GrantStage::Agreement)
            ->whereNotIn('judul', $knownChapterValues)
            ->with('contents')
            ->get();

        foreach ($customInfo as $info) {
            $this->customChapters[] = [
                'title' => $info->judul,
                'paragraphs' => $info->contents
                    ->sortBy('nomor_urut')
                    ->pluck('isi')
                    ->values()
                    ->all(),
            ];
        }
    }
}
