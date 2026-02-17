<?php

namespace App\Enums;

enum GrantStage: string
{
    case Planning = 'USULAN';
    case Agreement = 'PERJANJIAN';

    public function label(): string
    {
        return match ($this) {
            self::Planning => __('common.planning'),
            self::Agreement => __('common.agreement'),
        };
    }
}
