<?php

namespace App\Enums;

enum GrantType: string
{
    case Direct = 'LANGSUNG';
    case Planned = 'TERENCANA';
}
