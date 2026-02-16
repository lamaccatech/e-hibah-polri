<?php

namespace App\Livewire\GrantDocument;

use App\Enums\GrantGeneratedDocumentType;
use App\Enums\UnitLevel;
use App\Models\Grant;
use App\Models\OrgUnitChief;
use App\Repositories\GrantDocumentRepository;
use Livewire\Component;
use Spatie\LaravelPdf\Facades\Pdf;

class Generate extends Component
{
    public Grant $grant;

    public string $type;

    public string $documentDate = '';

    public bool $showPreview = false;

    public GrantGeneratedDocumentType $documentType;

    public ?OrgUnitChief $chief = null;

    public function mount(Grant $grant, string $type, GrantDocumentRepository $repo): void
    {
        $this->grant = $grant->load('orgUnit.parent', 'donor', 'numberings');

        $docType = GrantGeneratedDocumentType::fromSlug($type);
        abort_unless($docType, 404);

        $this->documentType = $docType;
        $this->type = $type;

        $unit = auth()->user()->unit;
        abort_unless($unit->level_unit === UnitLevel::SatuanKerja && $this->grant->id_satuan_kerja === $unit->id_user, 403);

        $this->chief = $repo->getActiveChief($this->grant);
    }

    public function preview(): void
    {
        $this->validate(['documentDate' => ['required', 'date']]);
        $this->showPreview = ! $this->showPreview;
    }

    public function download(GrantDocumentRepository $repo): mixed
    {
        $this->validate(['documentDate' => ['required', 'date']]);
        abort_unless($this->chief, 403);

        $data = $repo->getDocumentData($this->documentType, $this->grant, $this->documentDate, $this->chief);
        $template = $this->documentType->pdfView();
        $filename = $this->documentType->filename($this->grant);

        $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
        Pdf::view($template, $data)->format('a4')->save($tempPath);

        $repo->persistDocument($this->grant, $this->documentType, $this->documentDate, $tempPath);

        return response()->streamDownload(function () use ($tempPath) {
            readfile($tempPath);
            @unlink($tempPath);
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    public function render(GrantDocumentRepository $repo)
    {
        $previewData = null;
        if ($this->showPreview && $this->chief) {
            $previewData = $repo->getDocumentData($this->documentType, $this->grant, $this->documentDate, $this->chief);
        }

        return view('livewire.grant-document.generate', [
            'previewData' => $previewData,
            'previewView' => $this->documentType->pdfView(),
        ]);
    }
}
