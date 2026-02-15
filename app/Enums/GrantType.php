<?php

namespace App\Enums;

/**
 * LANGSUNG (Direct) — covers both:
 *   - Grants with proposals (ada_usulan=true): goes through planning (USULAN) stage first
 *   - Grants without proposals (ada_usulan=false): goes directly to agreement (PERJANJIAN) stage
 *
 * TERENCANA (Planned/HDR) — "Hibah Yang Direncanakan", a separate category
 */
enum GrantType: string
{
    case Direct = 'LANGSUNG';
    case Planned = 'TERENCANA';
}
