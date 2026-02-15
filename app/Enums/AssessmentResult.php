<?php

namespace App\Enums;

enum AssessmentResult: string
{
    case Fulfilled = 'TERPENUHI';
    case Revision = 'REVISI';
    case Rejected = 'DITOLAK';

    public function label(): string
    {
        return match ($this) {
            self::Fulfilled => __('page.grant-review.result-fulfilled'),
            self::Revision => __('page.grant-review.result-revision'),
            self::Rejected => __('page.grant-review.result-rejected'),
        };
    }
}
