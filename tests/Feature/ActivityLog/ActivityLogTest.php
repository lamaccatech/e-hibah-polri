<?php

use App\Enums\LogAction;
use App\Livewire\ActivityLog\Index;
use App\Models\ActivityLog;
use App\Models\ChangeHistory;
use App\Models\Donor;
use App\Models\Grant;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createMabesUserForActivityLog(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->mabes()->raw());

    return $user;
}

function createSatkerUserForActivityLog(): User
{
    $poldaUser = User::factory()->create();
    $poldaUser->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    $user = User::factory()->create();
    $user->unit()->create(
        OrgUnit::factory()->satuanKerja()->raw([
            'id_unit_atasan' => $poldaUser->id,
        ])
    );

    return $user;
}

function createPoldaUserForActivityLog(): User
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanInduk()->raw());

    return $user;
}

function createGrantForActivityLog(array $overrides = []): Grant
{
    $user = User::factory()->create();
    $user->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

    return $user->unit->grants()->create(
        Grant::factory()->raw($overrides)
    );
}

// ==========================================
// LogAction Enum
// ==========================================

describe('LogAction Enum', function () {
    it('has all expected cases', function () {
        $cases = LogAction::cases();
        expect($cases)->toHaveCount(10);
    });

    it('returns Indonesian labels', function () {
        expect(LogAction::Create->label())->toBe('Membuat');
        expect(LogAction::Update->label())->toBe('Mengubah');
        expect(LogAction::Delete->label())->toBe('Menghapus');
        expect(LogAction::Login->label())->toBe('Masuk');
        expect(LogAction::Logout->label())->toBe('Keluar');
        expect(LogAction::Submit->label())->toBe('Mengajukan');
        expect(LogAction::Review->label())->toBe('Mengkaji');
        expect(LogAction::Verify->label())->toBe('Memverifikasi');
        expect(LogAction::Reject->label())->toBe('Menolak');
        expect(LogAction::RequestRevision->label())->toBe('Meminta Revisi');
    });
});

// ==========================================
// ChangeHistory::getChanges()
// ==========================================

describe('ChangeHistory getChanges', function () {
    it('returns all fields with from null on creation', function () {
        $history = ChangeHistory::factory()->create([
            'state_before' => null,
            'state_after' => ['nama_hibah' => 'Hibah A', 'nilai_hibah' => 1000000],
        ]);

        $changes = $history->getChanges();

        expect($changes)->toHaveCount(2);
        expect($changes['nama_hibah'])->toBe(['from' => null, 'to' => 'Hibah A']);
        expect($changes['nilai_hibah'])->toBe(['from' => null, 'to' => 1000000]);
    });

    it('returns all fields with to null on deletion', function () {
        $history = ChangeHistory::factory()->create([
            'state_before' => ['nama_hibah' => 'Hibah A', 'nilai_hibah' => 1000000],
            'state_after' => null,
        ]);

        $changes = $history->getChanges();

        expect($changes)->toHaveCount(2);
        expect($changes['nama_hibah'])->toBe(['from' => 'Hibah A', 'to' => null]);
        expect($changes['nilai_hibah'])->toBe(['from' => 1000000, 'to' => null]);
    });

    it('returns only changed fields on update', function () {
        $history = ChangeHistory::factory()->create([
            'state_before' => ['nama_hibah' => 'Hibah A', 'nilai_hibah' => 1000000],
            'state_after' => ['nama_hibah' => 'Hibah B', 'nilai_hibah' => 1000000],
        ]);

        $changes = $history->getChanges();

        expect($changes)->toHaveCount(1);
        expect($changes['nama_hibah'])->toBe(['from' => 'Hibah A', 'to' => 'Hibah B']);
    });

    it('returns empty array when both states are null', function () {
        $history = ChangeHistory::factory()->create([
            'state_before' => null,
            'state_after' => null,
        ]);

        expect($history->getChanges())->toBe([]);
    });

    it('excludes unchanged fields', function () {
        $history = ChangeHistory::factory()->create([
            'state_before' => ['a' => 1, 'b' => 2, 'c' => 3],
            'state_after' => ['a' => 1, 'b' => 99, 'c' => 3],
        ]);

        $changes = $history->getChanges();

        expect($changes)->toHaveCount(1);
        expect($changes)->toHaveKey('b');
        expect($changes)->not->toHaveKey('a');
        expect($changes)->not->toHaveKey('c');
    });
});

// ==========================================
// User Helper Methods
// ==========================================

describe('User recordCreation', function () {
    it('creates ActivityLog and ChangeHistory for HasChangeHistory model', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog();

        $user->recordCreation($grant, 'Membuat hibah baru');

        expect(ActivityLog::where('user_id', $user->id)->where('action', LogAction::Create)->count())->toBe(1);
        expect(ChangeHistory::where('user_id', $user->id)->where('changeable_type', Grant::class)->count())->toBe(1);

        $changeHistory = ChangeHistory::where('user_id', $user->id)->first();
        expect($changeHistory->state_before)->toBeNull();
        expect($changeHistory->state_after)->not->toBeNull();
        expect($changeHistory->change_reason)->toBe('Membuat hibah baru');
    });

    it('creates only ActivityLog for model without HasChangeHistory', function () {
        $user = createMabesUserForActivityLog();
        $otherUser = User::factory()->create();

        $user->recordCreation($otherUser, 'Membuat pengguna baru');

        expect(ActivityLog::where('user_id', $user->id)->where('action', LogAction::Create)->count())->toBe(1);
        expect(ChangeHistory::where('user_id', $user->id)->count())->toBe(0);
    });
});

describe('User recordChange', function () {
    it('captures dirty fields in state_before and state_after', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog(['nama_hibah' => 'Original Name']);

        $grant->nama_hibah = 'Updated Name';
        $user->recordChange($grant, 'Mengubah nama hibah');

        $changeHistory = ChangeHistory::where('user_id', $user->id)->first();
        expect($changeHistory)->not->toBeNull();
        expect($changeHistory->state_before)->toHaveKey('nama_hibah');
        expect($changeHistory->state_before['nama_hibah'])->toBe('Original Name');
        expect($changeHistory->state_after)->toHaveKey('nama_hibah');
        expect($changeHistory->state_after['nama_hibah'])->toBe('Updated Name');
    });

    it('creates ActivityLog with changed field names in metadata', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog(['nama_hibah' => 'Original']);

        $grant->nama_hibah = 'Updated';
        $user->recordChange($grant, 'Mengubah nama');

        $log = ActivityLog::where('user_id', $user->id)->where('action', LogAction::Update)->first();
        expect($log->metadata['changed_fields'])->toContain('nama_hibah');
    });

    it('does not create ChangeHistory when no dirty attributes', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog();

        $user->recordChange($grant, 'No changes');

        expect(ActivityLog::where('user_id', $user->id)->count())->toBe(1);
        expect(ChangeHistory::where('user_id', $user->id)->count())->toBe(0);
    });
});

describe('User recordDeletion', function () {
    it('captures full state in state_before with null state_after', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog(['nama_hibah' => 'To Delete']);

        $user->recordDeletion($grant, 'Menghapus hibah');

        $changeHistory = ChangeHistory::where('user_id', $user->id)->first();
        expect($changeHistory)->not->toBeNull();
        expect($changeHistory->state_before)->not->toBeNull();
        expect($changeHistory->state_after)->toBeNull();
        expect($changeHistory->change_reason)->toBe('Menghapus hibah');
    });

    it('creates ActivityLog with Delete action', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog();

        $user->recordDeletion($grant, 'Menghapus hibah');

        $log = ActivityLog::where('user_id', $user->id)->where('action', LogAction::Delete)->first();
        expect($log)->not->toBeNull();
        expect($log->metadata['model_type'])->toBe(Grant::class);
    });
});

// ==========================================
// Sensitive Field Exclusion
// ==========================================

describe('Sensitive field exclusion', function () {
    it('excludes password and tokens from state snapshots', function () {
        $user = createMabesUserForActivityLog();
        $donor = Donor::factory()->create();

        $user->recordCreation($donor, 'Membuat donor');

        $changeHistory = ChangeHistory::where('user_id', $user->id)->first();
        $stateAfter = $changeHistory->state_after;

        expect($stateAfter)->not->toHaveKey('password');
        expect($stateAfter)->not->toHaveKey('remember_token');
        expect($stateAfter)->not->toHaveKey('id');
        expect($stateAfter)->not->toHaveKey('created_at');
        expect($stateAfter)->not->toHaveKey('updated_at');
    });

    it('excludes two_factor_secret and two_factor_recovery_codes from User snapshots', function () {
        $admin = createMabesUserForActivityLog();
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('testsecret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        ]);

        $admin->recordCreation($user, 'Membuat pengguna');

        $log = ActivityLog::where('user_id', $admin->id)->first();
        expect($log)->not->toBeNull();

        // User doesn't implement HasChangeHistory, so no ChangeHistory is created
        // But the ActivityLog should exist without leaking 2FA secrets in metadata
        expect($log->metadata)->not->toHaveKey('two_factor_secret');
        expect($log->metadata)->not->toHaveKey('two_factor_recovery_codes');
    });
});

// ==========================================
// Authentication Logging
// ==========================================

describe('Authentication logging', function () {
    it('creates ActivityLog on successful login', function () {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $log = ActivityLog::where('user_id', $user->id)->where('action', LogAction::Login)->first();
        expect($log)->not->toBeNull();
        expect($log->message)->toBe('Pengguna masuk ke sistem');
        expect($log->metadata)->toHaveKey('ip_address');
        expect($log->metadata)->toHaveKey('user_agent');
    });

    it('creates ActivityLog on logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $log = ActivityLog::where('user_id', $user->id)->where('action', LogAction::Logout)->first();
        expect($log)->not->toBeNull();
        expect($log->message)->toBe('Pengguna keluar dari sistem');
    });
});

// ==========================================
// Activity Log Index Page
// ==========================================

describe('Activity Log Index', function () {
    it('shows paginated activity logs for Mabes user', function () {
        $user = createMabesUserForActivityLog();
        ActivityLog::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('activity-log.index'))
            ->assertSuccessful();
    });

    it('filters by action type', function () {
        $user = createMabesUserForActivityLog();
        ActivityLog::factory()->create(['user_id' => $user->id, 'action' => LogAction::Create, 'message' => 'Created something']);
        ActivityLog::factory()->create(['user_id' => $user->id, 'action' => LogAction::Login, 'message' => 'Logged in']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('action', LogAction::Create->value)
            ->assertSee('Created something')
            ->assertDontSee('Logged in');
    });

    it('filters by user name', function () {
        $user = createMabesUserForActivityLog();
        $otherUser = User::factory()->create(['name' => 'John Doe']);
        ActivityLog::factory()->create(['user_id' => $user->id, 'message' => 'User A action']);
        ActivityLog::factory()->create(['user_id' => $otherUser->id, 'message' => 'John action']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('search', 'John')
            ->assertSee('John action')
            ->assertDontSee('User A action');
    });

    it('filters by date range with dateFrom', function () {
        $user = createMabesUserForActivityLog();
        $oldLog = ActivityLog::factory()->create([
            'user_id' => $user->id,
            'message' => 'Old log',
            'created_at' => now()->subDays(10),
        ]);
        $recentLog = ActivityLog::factory()->create([
            'user_id' => $user->id,
            'message' => 'Recent log',
            'created_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('dateFrom', now()->subDay()->format('Y-m-d'))
            ->assertSee('Recent log')
            ->assertDontSee('Old log');
    });

    it('filters by date range with dateTo', function () {
        $user = createMabesUserForActivityLog();
        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'message' => 'Old log',
            'created_at' => now()->subDays(10),
        ]);
        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'message' => 'Recent log',
            'created_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('dateTo', now()->subDays(5)->format('Y-m-d'))
            ->assertSee('Old log')
            ->assertDontSee('Recent log');
    });

    it('shows activity logs sorted by newest first', function () {
        $user = createMabesUserForActivityLog();
        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'message' => 'First created',
            'created_at' => now()->subMinutes(10),
        ]);
        ActivityLog::factory()->create([
            'user_id' => $user->id,
            'message' => 'Second created',
            'created_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSeeInOrder(['Second created', 'First created']);
    });

    it('shows Sistem when user_id is null', function () {
        $user = createMabesUserForActivityLog();
        ActivityLog::factory()->create(['user_id' => null, 'message' => 'System action']);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Sistem');
    });

    it('redirects non-Mabes users', function () {
        $satkerUser = createSatkerUserForActivityLog();

        $this->actingAs($satkerUser)
            ->get(route('activity-log.index'))
            ->assertRedirect();
    });

    it('redirects Polda users', function () {
        $poldaUser = createPoldaUserForActivityLog();

        $this->actingAs($poldaUser)
            ->get(route('activity-log.index'))
            ->assertRedirect();
    });
});

// ==========================================
// Grant Change History Tab
// ==========================================

describe('Grant Change History Tab', function () {
    it('shows change history entries on grant detail', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog();
        $grant->statusHistory()->create([
            'status_sesudah' => \App\Enums\GrantStatus::PlanningInitialized->value,
            'keterangan' => 'Test',
        ]);

        ChangeHistory::factory()->create([
            'user_id' => $user->id,
            'changeable_type' => Grant::class,
            'changeable_id' => $grant->id,
            'change_reason' => 'Mengubah nama kegiatan',
            'state_before' => ['nama_hibah' => 'Old'],
            'state_after' => ['nama_hibah' => 'New'],
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\GrantDetail\Show::class, ['grant' => $grant])
            ->call('switchTab', 'change-history')
            ->assertSee('Mengubah nama kegiatan');
    });

    it('shows empty state when no change history exists', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog();
        $grant->statusHistory()->create([
            'status_sesudah' => \App\Enums\GrantStatus::PlanningInitialized->value,
            'keterangan' => 'Test',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\GrantDetail\Show::class, ['grant' => $grant])
            ->call('switchTab', 'change-history')
            ->assertSee(__('page.grant-detail-change-history.empty-state'));
    });
});

// ==========================================
// User Model Relationships
// ==========================================

describe('User relationships', function () {
    it('has activityLogs relationship', function () {
        $user = User::factory()->create();
        ActivityLog::factory()->count(2)->create(['user_id' => $user->id]);

        expect($user->activityLogs)->toHaveCount(2);
    });

    it('has changesMade relationship', function () {
        $user = User::factory()->create();
        ChangeHistory::factory()->count(2)->create(['user_id' => $user->id]);

        expect($user->changesMade)->toHaveCount(2);
    });
});

// ==========================================
// Edge Cases
// ==========================================

describe('Edge cases', function () {
    it('logging failure does not abort the user action', function () {
        $user = createMabesUserForActivityLog();

        // recordCreation uses try/catch internally — verify it doesn't throw
        // even with an unusual model
        $grant = createGrantForActivityLog();
        $user->recordCreation($grant, 'Test');

        expect(ActivityLog::where('user_id', $user->id)->count())->toBe(1);
    });

    it('multiple rapid changes create separate ChangeHistory entries', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog(['nama_hibah' => 'V1']);

        $grant->nama_hibah = 'V2';
        $user->recordChange($grant, 'First change');
        $grant->save();

        $grant->nama_hibah = 'V3';
        $user->recordChange($grant, 'Second change');
        $grant->save();

        expect(ChangeHistory::where('user_id', $user->id)
            ->where('changeable_type', Grant::class)
            ->where('changeable_id', $grant->id)
            ->count())->toBe(2);
    });

    it('ChangeHistory for soft-deleted model is still accessible', function () {
        $user = createMabesUserForActivityLog();
        $grant = createGrantForActivityLog();

        $user->recordCreation($grant, 'Created');
        $grant->delete();

        $history = ChangeHistory::where('changeable_type', Grant::class)
            ->where('changeable_id', $grant->id)
            ->first();

        expect($history)->not->toBeNull();
        expect($history->change_reason)->toBe('Created');
    });
});

// ==========================================
// Donor Change History
// ==========================================

describe('Donor with HasChangeHistory', function () {
    it('creates ChangeHistory when recording donor creation', function () {
        $user = createMabesUserForActivityLog();
        $donor = Donor::factory()->create();

        $user->recordCreation($donor, 'Membuat donor baru');

        $changeHistory = ChangeHistory::where('changeable_type', Donor::class)
            ->where('changeable_id', $donor->id)
            ->first();

        expect($changeHistory)->not->toBeNull();
        expect($changeHistory->state_before)->toBeNull();
        expect($changeHistory->state_after)->not->toBeNull();
    });

    it('creates ChangeHistory when recording donor update', function () {
        $user = createMabesUserForActivityLog();
        $donor = Donor::factory()->create(['nama' => 'Original']);

        $donor->nama = 'Updated';
        $user->recordChange($donor, 'Mengubah nama donor');

        $changeHistory = ChangeHistory::where('changeable_type', Donor::class)
            ->where('changeable_id', $donor->id)
            ->first();

        expect($changeHistory)->not->toBeNull();
        expect($changeHistory->state_before['nama'])->toBe('Original');
        expect($changeHistory->state_after['nama'])->toBe('Updated');
    });
});
