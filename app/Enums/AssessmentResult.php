<?php

namespace App\Enums;

enum AssessmentResult: string
{
    case Fulfilled = 'TERPENUHI';
    case Revision = 'REVISI';
    case Rejected = 'DITOLAK';
}
