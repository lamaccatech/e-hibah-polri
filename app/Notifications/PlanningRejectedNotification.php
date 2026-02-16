<?php

namespace App\Notifications;

use App\Models\Grant;
use Illuminate\Notifications\Notification;

class PlanningRejectedNotification extends Notification
{
    public function __construct(
        public Grant $grant,
        public string $rejectedBy,
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
            'rejected_by' => $this->rejectedBy,
        ];
    }
}
