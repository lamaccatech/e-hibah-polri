<?php

namespace App\Enums;

enum GrantGeneratedDocumentType: string
{
    case ProposalDocument = 'NASKAH_USULAN';
    case AssessmentDocument = 'KAJIAN_USULAN';
    case ReadinessDocument = 'KESIAPAN_HIBAH';
}
