<?php

use App\Livewire\NotificationBell;
use App\Models\OrgUnit;
use App\Models\User;
use App\Notifications\PlanningNumberIssuedNotification;
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

    it('routes planning number issued notification to grant planning index', function () {
        $satker = createUserWithUnit('satuan_kerja');

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $satker->notify(new PlanningNumberIssuedNotification($grant, 'USL/001/2025'));

        $notification = $satker->notifications()->latest()->first();

        $this->actingAs($satker);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-planning.index'));
    });

    it('routes submitted notification to grant review index', function () {
        $polda = createUserWithUnit();
        $satker = \App\Models\User::factory()->create();
        $satker->unit()->create(\App\Models\OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ]));

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->planned()->raw()
        );

        $polda->notify(new PlanningSubmittedNotification($grant));

        $notification = $polda->notifications()->latest()->first();

        $this->actingAs($polda);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-review.index'));
    });

    it('limits display to 10 most recent notifications', function () {
        $polda = createUserWithUnit();
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ]));

        // Create 12 grants and notifications
        for ($i = 0; $i < 12; $i++) {
            $grant = $satker->unit->grants()->create(
                \App\Models\Grant::factory()->planned()->raw()
            );
            $polda->notify(new PlanningSubmittedNotification($grant));
        }

        expect($polda->unreadNotifications()->count())->toBe(12);

        $this->actingAs($polda);

        $component = Livewire::test(NotificationBell::class);
        $notifications = $component->viewData('notifications');
        expect($notifications->count())->toBeLessThanOrEqual(10);
    });

    it('routes agreement rejection notification to grant detail page', function () {
        $satker = createUserWithUnit('satuan_kerja');

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->directAgreement()->raw()
        );

        $satker->notify(new \App\Notifications\AgreementRejectedNotification($grant, 'Polda'));

        $notification = $satker->notifications()->latest()->first();

        $this->actingAs($satker);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-detail.show', $grant->id));
    });

    // Known limitation: agreement revision routes to grant-planning.edit (see spec)
    it('routes agreement revision notification to grant planning edit page', function () {
        $satker = createUserWithUnit('satuan_kerja');

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->directAgreement()->raw()
        );

        $satker->notify(new \App\Notifications\AgreementRevisionRequestedNotification($grant, 'Polda'));

        $notification = $satker->notifications()->latest()->first();

        $this->actingAs($satker);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-planning.edit', $grant->id));
    });

    // Known limitation: agreement number issued falls to grant_id catch-all
    it('routes agreement number issued notification to grant review index', function () {
        $satker = createUserWithUnit('satuan_kerja');

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->directAgreement()->raw()
        );

        $satker->notify(new \App\Notifications\AgreementNumberIssuedNotification($grant, 'NPH/001/2025'));

        $notification = $satker->notifications()->latest()->first();

        $this->actingAs($satker);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-review.index'));
    });

    // Known limitation: agreement submitted falls to grant_id catch-all
    it('routes agreement submitted notification to grant review index', function () {
        $polda = createUserWithUnit();
        $satker = User::factory()->create();
        $satker->unit()->create(OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $polda->id,
        ]));

        $grant = $satker->unit->grants()->create(
            \App\Models\Grant::factory()->directAgreement()->raw()
        );

        $polda->notify(new \App\Notifications\AgreementSubmittedNotification($grant));

        $notification = $polda->notifications()->latest()->first();

        $this->actingAs($polda);

        $component = Livewire::test(NotificationBell::class);
        $url = $component->instance()->getUrl($notification);

        expect($url)->toBe(route('grant-review.index'));
    });

    it('stores correct data schema for agreement notifications', function () {
        $grant = createUserWithUnit('satuan_kerja')->unit->grants()->create(
            \App\Models\Grant::factory()->directAgreement()->raw()
        );

        // Each notification tested on a fresh user to avoid ordering ambiguity
        $user1 = createUserWithUnit();
        $user1->notify(new \App\Notifications\AgreementSubmittedNotification($grant));
        expect($user1->notifications()->first()->data)->toHaveKeys(['grant_id', 'grant_name', 'unit_name']);

        $user2 = User::factory()->create();
        $user2->notify(new \App\Notifications\AgreementRejectedNotification($grant, 'Polda'));
        expect($user2->notifications()->first()->data)->toHaveKeys(['grant_id', 'grant_name', 'rejected_by']);

        $user3 = User::factory()->create();
        $user3->notify(new \App\Notifications\AgreementRevisionRequestedNotification($grant, 'Polda'));
        expect($user3->notifications()->first()->data)->toHaveKeys(['grant_id', 'grant_name', 'revision_requested_by']);

        $user4 = User::factory()->create();
        $user4->notify(new \App\Notifications\AgreementNumberIssuedNotification($grant, 'NPH/001/2025'));
        expect($user4->notifications()->first()->data)->toHaveKeys(['grant_id', 'grant_name', 'agreement_number']);
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
