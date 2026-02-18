<?php

namespace App\Listeners;

use App\Enums\LogAction;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $event->user->activityLogs()->create([
            'action' => LogAction::Login,
            'message' => 'Pengguna masuk ke sistem',
            'metadata' => [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
