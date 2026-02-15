<?php

namespace App\Notifications;

use App\Models\Grant;
use Illuminate\Notifications\Notification;

class PlanningSubmittedNotification extends Notification
{
    public function __construct(
        public Grant $grant,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'grant_id' => $this->grant->id,
            'grant_name' => $this->grant->nama_hibah,
            'unit_name' => $this->grant->orgUnit->nama_unit,
        ];
    }
}
