<?php

// SPEC: Settings (Profile, Password, Appearance, Two-Factor Authentication)
// See specs/features/settings.md for full feature spec.

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Models\OrgUnit;
use App\Models\User;
use Livewire\Livewire;

function createVerifiedUser(): User
{
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $user->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

    return $user;
}

function createUnverifiedUser(): User
{
    $user = User::factory()->unverified()->create();
    $user->unit()->create(OrgUnit::factory()->satuanKerja()->raw());

    return $user;
}

describe('Settings — Profile', function () {
    it('allows user to view profile settings page', function () {
        $user = createVerifiedUser();

        $this->actingAs($user)
            ->get('/settings/profile')
            ->assertSuccessful();
    });

    it('allows user to update their name', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'Nama Baru')
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();
        expect($user->name)->toBe('Nama Baru');
    });

    it('resets email_verified_at when email is changed', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        expect($user->email_verified_at)->not->toBeNull();

        Livewire::test(Profile::class)
            ->set('name', $user->name)
            ->set('email', 'newemail@example.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();
        expect($user->email)->toBe('newemail@example.com');
        expect($user->email_verified_at)->toBeNull();
    });

    it('fails to update email to an existing email', function () {
        $user = createVerifiedUser();
        $other = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', $user->name)
            ->set('email', $other->email)
            ->call('updateProfileInformation')
            ->assertHasErrors(['email']);
    });

    it('does not show resend link when User does not implement MustVerifyEmail', function () {
        // SPEC: User model does not implement MustVerifyEmail,
        // so resend verification link is never shown.
        $user = createUnverifiedUser();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertDontSee(__('page.profile.resend-link'));
    });
});

describe('Settings — Password', function () {
    it('allows user to update password with correct current password', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasNoErrors();
    });

    it('fails to update password with wrong current password', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    });

    it('fails to update password with mismatched confirmation', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'different-password')
            ->call('updatePassword')
            ->assertHasErrors(['password']);
    });

    it('clears password fields after successful update', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Password::class)
            ->set('current_password', 'password')
            ->set('password', 'new-password-123')
            ->set('password_confirmation', 'new-password-123')
            ->call('updatePassword')
            ->assertSet('current_password', '')
            ->assertSet('password', '')
            ->assertSet('password_confirmation', '');
    });
});

describe('Settings — Appearance', function () {
    it('renders appearance page with theme options', function () {
        $user = createVerifiedUser();

        $this->actingAs($user)
            ->get('/settings/appearance')
            ->assertSuccessful();
    });

    it('displays Light, Dark, and System theme options', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(Appearance::class)
            ->assertSeeText(__('page.appearance.light'))
            ->assertSeeText(__('page.appearance.dark'))
            ->assertSeeText(__('page.appearance.system'));
    });
});

describe('Settings — Two-Factor Authentication', function () {
    it('renders two-factor settings page', function () {
        $user = createVerifiedUser();

        // Password confirmation is required; simulate confirmed state
        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get('/settings/two-factor')
            ->assertSuccessful();
    });

    it('allows user to enable 2FA', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(TwoFactor::class)
            ->assertSet('twoFactorEnabled', false)
            ->call('enable')
            ->assertSet('showModal', true)
            ->assertNotSet('qrCodeSvg', '');
    });

    it('displays QR code and manual setup key when enabling 2FA', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        $component = Livewire::test(TwoFactor::class)
            ->call('enable')
            ->assertSet('showModal', true);

        expect($component->get('qrCodeSvg'))->not->toBeEmpty();
        expect($component->get('manualSetupKey'))->not->toBeEmpty();
    });

    it('allows user to confirm 2FA with valid code', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        $component = Livewire::test(TwoFactor::class)
            ->call('enable');

        // Get the secret to generate a valid TOTP code
        $user->refresh();
        $secret = decrypt($user->two_factor_secret);

        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $validCode = $google2fa->getCurrentOtp($secret);

        $component
            ->call('showVerificationIfNecessary')
            ->assertSet('showVerificationStep', true)
            ->set('code', $validCode)
            ->call('confirmTwoFactor')
            ->assertSet('twoFactorEnabled', true)
            ->assertSet('showModal', false);
    });

    it('fails to confirm 2FA with invalid code', function () {
        $user = createVerifiedUser();

        $this->actingAs($user);

        Livewire::test(TwoFactor::class)
            ->call('enable')
            ->call('showVerificationIfNecessary')
            ->set('code', '000000')
            ->call('confirmTwoFactor')
            ->assertHasErrors();
    });

    it('allows user to disable 2FA', function () {
        $user = createVerifiedUser();

        // Enable 2FA directly
        app(\Laravel\Fortify\Actions\EnableTwoFactorAuthentication::class)($user);

        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $code = $google2fa->getCurrentOtp($secret);

        app(\Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication::class)($user, $code);

        $this->actingAs($user);

        Livewire::test(TwoFactor::class)
            ->assertSet('twoFactorEnabled', true)
            ->call('disable')
            ->assertSet('twoFactorEnabled', false);
    });

    it('shows recovery codes when 2FA is enabled', function () {
        $user = createVerifiedUser();

        app(\Laravel\Fortify\Actions\EnableTwoFactorAuthentication::class)($user);

        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $code = $google2fa->getCurrentOtp($secret);

        app(\Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication::class)($user, $code);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\Settings\TwoFactor\RecoveryCodes::class);

        expect($component->get('recoveryCodes'))->not->toBeEmpty();
    });

    it('loads recovery codes from encrypted storage', function () {
        $user = createVerifiedUser();

        app(\Laravel\Fortify\Actions\EnableTwoFactorAuthentication::class)($user);

        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $code = $google2fa->getCurrentOtp($secret);

        app(\Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication::class)($user, $code);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\Settings\TwoFactor\RecoveryCodes::class);
        $codes = $component->get('recoveryCodes');

        expect($codes)->toBeArray();
        expect($codes)->not->toBeEmpty();
        expect(count($codes))->toBe(8);
    });

    it('allows user to regenerate recovery codes', function () {
        $user = createVerifiedUser();

        app(\Laravel\Fortify\Actions\EnableTwoFactorAuthentication::class)($user);

        $user->refresh();
        $secret = decrypt($user->two_factor_secret);
        $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
        $code = $google2fa->getCurrentOtp($secret);

        app(\Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication::class)($user, $code);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\Settings\TwoFactor\RecoveryCodes::class);
        $originalCodes = $component->get('recoveryCodes');

        $component->call('regenerateRecoveryCodes');
        $newCodes = $component->get('recoveryCodes');

        expect($newCodes)->not->toBeEmpty();
        expect($newCodes)->not->toBe($originalCodes);
    });
});

describe('Settings — Access Control', function () {
    it('redirects /settings to /settings/profile', function () {
        $user = createVerifiedUser();

        $this->actingAs($user)
            ->get('/settings')
            ->assertRedirect('/settings/profile');
    });

    it('redirects unauthenticated user to login', function () {
        $this->get('/settings/profile')
            ->assertRedirect('/login');
    });

    it('allows unverified user to access profile page', function () {
        $user = createUnverifiedUser();

        $this->actingAs($user)
            ->get('/settings/profile')
            ->assertSuccessful();
    });

    it('allows unverified user to access password page', function () {
        // SPEC: User model does not implement MustVerifyEmail,
        // so verified middleware does not enforce email verification.
        $user = createUnverifiedUser();

        $this->actingAs($user)
            ->get('/settings/password')
            ->assertSuccessful();
    });

    it('allows unverified user to access appearance page', function () {
        $user = createUnverifiedUser();

        $this->actingAs($user)
            ->get('/settings/appearance')
            ->assertSuccessful();
    });

    it('redirects user to password confirmation for two-factor page', function () {
        // SPEC: Two-factor page requires password.confirm middleware
        $user = createVerifiedUser();

        $this->actingAs($user)
            ->get('/settings/two-factor')
            ->assertRedirect(route('password.confirm'));
    });
});
