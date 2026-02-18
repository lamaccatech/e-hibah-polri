<?php

namespace App\Enums;

enum AssessmentAspect: string
{
    case Technical = 'TEKNIS';
    case Economic = 'EKONOMIS';
    case Political = 'POLITIS';
    case Strategic = 'STRATEGIS';

    public function label(): string
    {
        return match ($this) {
            self::Technical => __('common.assessment-aspect.technical'),
            self::Economic => __('common.assessment-aspect.economic'),
            self::Political => __('common.assessment-aspect.political'),
            self::Strategic => __('common.assessment-aspect.strategic'),
        };
    }

    /**
     * @return string[]
     */
    public function prompts(): array
    {
        return match ($this) {
            self::Technical => [
                __('common.assessment-aspect.prompt-technical-1'),
                __('common.assessment-aspect.prompt-technical-2'),
            ],
            self::Economic => [
                __('common.assessment-aspect.prompt-economic-1'),
                __('common.assessment-aspect.prompt-economic-2'),
            ],
            self::Political => [
                __('common.assessment-aspect.prompt-political-1'),
                __('common.assessment-aspect.prompt-political-2'),
            ],
            self::Strategic => [
                __('common.assessment-aspect.prompt-strategic-1'),
                __('common.assessment-aspect.prompt-strategic-2'),
            ],
        };
    }
}
