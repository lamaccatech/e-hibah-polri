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
            self::AssessmentDocument => __('common.grant-generated-document-type.assessment-document'),
            self::ProposalDocument => __('common.grant-generated-document-type.proposal-document'),
            self::ReadinessDocument => __('common.grant-generated-document-type.readiness-document'),
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
            self::AssessmentDocument => __('common.grant-generated-document-type.filename-assessment'),
            self::ProposalDocument => __('common.grant-generated-document-type.filename-proposal'),
            self::ReadinessDocument => __('common.grant-generated-document-type.filename-readiness'),
        };

        return "{$base} - {$grant->nama_hibah}.pdf";
    }
}
