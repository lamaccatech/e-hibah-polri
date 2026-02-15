<?php

namespace App\Http\Middleware;

use App\Enums\UnitLevel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSatker
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->unit || $user->unit->level_unit !== UnitLevel::SatuanKerja) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
