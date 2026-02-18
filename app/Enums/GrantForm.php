<?php

namespace App\Enums;

enum GrantForm: string
{
    case Money = 'UANG';
    case Goods = 'BARANG';
    case Services = 'JASA';

    public function label(): string
    {
        return match ($this) {
            self::Money => __('common.grant-form.money'),
            self::Goods => __('common.grant-form.goods'),
            self::Services => __('common.grant-form.services'),
        };
    }
}
