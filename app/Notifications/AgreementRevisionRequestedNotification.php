<?php

namespace App\Notifications;

use App\Models\Grant;
use Illuminate\Notifications\Notification;

class AgreementRevisionRequestedNotification extends Notification
{
    public function __construct(
        public Grant $grant,
        public string $requestedBy,
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
            'revision_requested_by' => $this->requestedBy,
        ];
    }
}
