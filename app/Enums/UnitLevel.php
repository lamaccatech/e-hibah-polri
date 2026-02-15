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
            self::Mabes => 'Mabes',
            self::SatuanInduk => 'Satuan Induk',
            self::SatuanKerja => 'Satuan Kerja',
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
