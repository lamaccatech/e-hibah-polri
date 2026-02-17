# Feature: User Settings

## Overview

Authenticated users can manage their account settings: profile information, password, appearance (theme), and two-factor authentication. Settings are accessed via the `/settings` path which redirects to the profile page by default.

## Business Rules

- All authenticated users can access settings
- Profile page is accessible without email verification (requires only `auth`)
- Password, appearance, and two-factor pages require both `auth` and `verified`
- Two-factor setup requires password confirmation when `confirmPassword` is enabled in Fortify config
- Recovery codes are generated when 2FA is enabled and can be regenerated at any time

## Routes

| Method | Path | Name | Description | Middleware |
|--------|------|------|-------------|------------|
| GET | /settings | — | Redirect to /settings/profile | auth |
| GET | /settings/profile | profile.edit | Profile settings | auth |
| GET | /settings/password | user-password.edit | Password settings | auth, verified |
| GET | /settings/appearance | appearance.edit | Theme settings | auth, verified |
| GET | /settings/two-factor | two-factor.show | 2FA settings | auth, verified, password.confirm* |

*`password.confirm` middleware is conditional on Fortify feature config.

## Profile Settings

**Component:** `Settings\Profile`

### Fields

| Field | Type | Rules | Description |
|-------|------|-------|-------------|
| name | string | required | Display name |
| email | email | required, unique (except current) | Login email |

### Behavior
- On save, updates user record
- If email is changed, `email_verified_at` is set to null (re-verification required)
- Unverified email shows a "resend verification link" option
- Includes a nested `DeleteUserForm` component for account deletion (only visible when email is verified)

### Delete Account
- Requires password confirmation via modal
- Deletes the user account

## Password Settings

**Component:** `Settings\Password`

### Fields

| Field | Type | Rules | Description |
|-------|------|-------|-------------|
| current_password | password | required, must match current | Current password |
| password | password | required, confirmed, min:8 | New password |
| password_confirmation | password | required | New password confirmation |

### Behavior
- Validates current password before updating
- Resets all password fields after update (success or validation failure)
- Dispatches `password-updated` event for UI feedback

## Appearance Settings

**Component:** `Settings\Appearance`

### Behavior
- Client-side only — uses Flux's `$flux.appearance` Alpine.js binding
- Three options: Light, Dark, System
- No server-side persistence (stored in browser via Flux)

## Two-Factor Authentication

**Component:** `Settings\TwoFactor`

### States

| State | Description |
|-------|-------------|
| Disabled | Shows "Enable" button, disabled badge |
| Enabling (modal open) | Shows QR code + manual setup key |
| Verifying (modal step 2) | Shows OTP input for confirmation |
| Enabled | Shows "Enabled" badge, recovery codes, "Disable" button |

### Enable Flow
1. User clicks "Enable 2FA"
2. `EnableTwoFactorAuthentication` Fortify action runs
3. Modal opens showing QR code SVG and manual setup key
4. User clicks "Continue" → verification step appears (if `confirmPassword` enabled)
5. User enters 6-digit TOTP code
6. `ConfirmTwoFactorAuthentication` Fortify action validates and confirms
7. Modal closes, status shows "Enabled"

### Disable Flow
1. User clicks "Disable 2FA"
2. `DisableTwoFactorAuthentication` Fortify action runs
3. Status reverts to "Disabled"

### Recovery Codes

**Component:** `Settings\TwoFactor\RecoveryCodes`

- Shown only when 2FA is enabled
- Codes are hidden by default, toggle to show/hide
- "Regenerate" button generates new codes via Fortify's `GenerateNewRecoveryCodes` action
- Codes are decrypted from `two_factor_recovery_codes` column

## Test Scenarios

### Profile
1. User can view profile settings page
2. User can update their name
3. User can update their email — `email_verified_at` reset to null
4. Updating email to existing email fails validation
5. Unverified email shows resend verification link

### Password
6. User can update password with correct current password
7. Update fails with wrong current password
8. Update fails with mismatched confirmation
9. Password fields are cleared after update

### Appearance
10. Appearance page renders with theme options (Light, Dark, System)

### Two-Factor Authentication
11. User can enable 2FA — QR code and setup key displayed
12. User confirms 2FA with valid TOTP code — 2FA enabled
13. Confirmation fails with invalid code
14. User can disable 2FA
15. Recovery codes are shown when 2FA is enabled
16. User can regenerate recovery codes

### Access Control
17. Unauthenticated user → redirected to login
18. Unverified user can access profile page
19. Unverified user cannot access password/appearance/2FA pages
