<?php

namespace App\Enums;

enum LogAction: string
{
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';
}
