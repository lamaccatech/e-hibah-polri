<?php

namespace App\Enums;

use App\Models\Grant;

enum GrantGeneratedDocumentType: string
{
    case ProposalDocument = 'NASKAH_USULAN';
    case AssessmentDocument = 'KAJIAN_USULAN';
    case ReadinessDocument = 'KESIAPAN_HIBAH';

    public function label(): string
    {
        return match ($this) {
            self::AssessmentDocument => 'Kajian Usulan',
            self::ProposalDocument => 'Naskah Usulan',
            self::ReadinessDocument => 'Kesiapan Penerimaan Hibah Langsung',
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::AssessmentDocument => 'assessment',
            self::ProposalDocument => 'proposal',
            self::ReadinessDocument => 'readiness',
        };
    }

    public static function fromSlug(string $slug): ?self
    {
        return collect(self::cases())->first(fn ($case) => $case->slug() === $slug);
    }

    public function pdfView(): string
    {
        return match ($this) {
            self::AssessmentDocument => 'pdf.assessment-document',
            self::ProposalDocument => 'pdf.proposal-document',
            self::ReadinessDocument => 'pdf.readiness-document',
        };
    }

    public function filename(Grant $grant): string
    {
        $base = match ($this) {
            self::AssessmentDocument => 'Kajian Usulan',
            self::ProposalDocument => 'Naskah Usulan',
            self::ReadinessDocument => 'Kesiapan Hibah',
        };

        return "{$base} - {$grant->nama_hibah}.pdf";
    }
}
