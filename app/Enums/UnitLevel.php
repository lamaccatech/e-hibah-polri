<?php

namespace App\Enums;

enum UnitLevel: string
{
    case Mabes = 'mabes';
    case SatuanInduk = 'satuan_induk';
    case SatuanKerja = 'satuan_kerja';

    public function label(): string
    {
        return match ($this) {
            self::Mabes => __('common.unit-level.mabes'),
            self::SatuanInduk => __('common.unit-level.satuan-induk'),
            self::SatuanKerja => __('common.unit-level.satuan-kerja'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $level) => [$level->value => $level->label()])
            ->all();
    }
}
