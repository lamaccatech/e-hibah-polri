<?php

use App\Livewire\NotificationBell;
use App\Models\OrgUnit;
use App\Models\User;
use App\Notifications\PlanningRejectedNotification;
use App\Notifications\PlanningRevisionRequestedNotification;
use App\Notifications\PlanningSubmittedNotification;
use Livewire\Livewire;

function createUserWithUnit(string $level = 'satuan_induk'): User
{
    $user = User::factory()->create();
    $factory = OrgUnit::factory();

    $unit = match ($level) {
        'satuan_induk' => $factory->satuanInduk(),
        'satuan_kerja' => $factory->satuanKerja(),
        'mabes' => $factory->mabes(),
    };

    $user->unit()->create($unit->raw());

    return $user;
}

describe('Notification Bell', function () {
    it('renders with zero unread count', function () {
        $user = createUserWithUnit();

        $this->actingAs($user);

        Livewire::test(NotificationBell::class)
            ->assertSeeText(__('component.notification.empty'));
    });

    it('shows unread notification count', function () {
        $polda = createUserWithUnit();
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ]));

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $polda->notify(new PlanningSubmittedNotification($grant));

        $this->actingAs($polda);

        Livewire::test(NotificationBell::class)
            ->assertSee($grant->nama_hibah)
            ->assertSee($satker->unit->nama_unit);
    });

    it('marks a single notification as read', function () {
        $polda = createUserWithUnit();
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ]));

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $polda->notify(new PlanningSubmittedNotification($grant));

        $notification = $polda->unreadNotifications()->first();

        $this->actingAs($polda);

        Livewire::test(NotificationBell::class)
            ->call('markAsRead', $notification->id);

        expect($polda->unreadNotifications()->count())->toBe(0);
    });

    it('routes rejection notification to grant detail page', function () {
        $satker = createUserWithUnit('satuan_kerja');

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $satker->notify(new PlanningRejectedNotification($grant, 'Polda'));

        $notification = $satker->notifications()->latest()->first();

        $this->actingAs($satker);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-detail.show', $grant->id));
    });

    it('routes revision notification to grant planning edit page', function () {
        $satker = createUserWithUnit('satuan_kerja');

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $satker->notify(new PlanningRevisionRequestedNotification($grant, 'Polda'));

        $notification = $satker->notifications()->latest()->first();

        $this->actingAs($satker);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-planning.edit', $grant->id));
    });

    it('marks all notifications as read', function () {
        $polda = createUserWithUnit();
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ]));

        $grant1 = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );
        $grant2 = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $polda->notify(new PlanningSubmittedNotification($grant1));
        $polda->notify(new PlanningSubmittedNotification($grant2));

        expect($polda->unreadNotifications()->count())->toBe(2);

        $this->actingAs($polda);

        Livewire::test(NotificationBell::class)
            ->call('markAllAsRead');

        expect($polda->unreadNotifications()->count())->toBe(0);
    });
});
