<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead(string $notificationId): void
    {
        $notification = auth()->user()->notifications()->find($notificationId);

        $notification?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function getUrl(DatabaseNotification $notification): ?string
    {
        if (isset($notification->data['planning_number'])) {
            return route('grant-planning.index');
        }

        if (isset($notification->data['rejected_by'])) {
            return route('grant-detail.show', $notification->data['grant_id']);
        }

        if (isset($notification->data['revision_requested_by'])) {
            return route('grant-planning.edit', $notification->data['grant_id']);
        }

        if (isset($notification->data['grant_id'])) {
            return route('grant-review.index');
        }

        return null;
    }

    public function render(): View
    {
        $notifications = auth()->user()->notifications()->latest()->limit(10)->get();
        $unreadCount = auth()->user()->unreadNotifications()->count();

        return view('livewire.notification-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
