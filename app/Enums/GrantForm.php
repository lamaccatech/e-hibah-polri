<?php

namespace App\Enums;

enum GrantForm: string
{
    case Money = 'UANG';
    case Goods = 'BARANG';
    case Services = 'JASA';
}
