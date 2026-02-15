<?php

namespace App\Enums;

enum FileType: string
{
    case Document = 'document';
    case Signature = 'signature';
    case DraftAgreement = 'draft_agreement';
    case Agreement = 'agreement';
    case SupportingDocument = 'supporting_document';
}
