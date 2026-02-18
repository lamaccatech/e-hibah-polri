<?php

namespace App\Listeners;

use App\Enums\LogAction;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        if ($event->user === null) {
            return;
        }

        $event->user->activityLogs()->create([
            'action' => LogAction::Logout,
            'message' => 'Pengguna keluar dari sistem',
        ]);
    }
}
