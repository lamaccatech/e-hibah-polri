# Feature: User Authentication

## Overview

Users log in with email and password to access the application. There is no self-registration — all accounts are created by Mabes (headquarters). After successful login, users are redirected to the dashboard.

## Business Rules

- Users authenticate using their email address and password
- No self-registration feature — accounts are provisioned by Mabes
- After successful login, users are redirected to the dashboard (`/dashboard`)
- Unauthenticated users accessing protected pages are redirected to the login page
- Users can log out, which invalidates their session
- Login attempts are rate-limited (5 per minute per email/IP combination)

## Routes

| Method | Path       | Name          | Description             | Auth |
|--------|------------|---------------|-------------------------|------|
| GET    | /          | home          | Redirect to /login      | No   |
| GET    | /login     | login         | Show login form         | No   |
| POST   | /login     | —             | Process login attempt   | No   |
| POST   | /logout    | logout        | Log out current user    | Yes  |
| GET    | /dashboard | dashboard     | Authenticated home page | Yes  |

## Login Form Fields

| Field    | Type     | Rules                  |
|----------|----------|------------------------|
| email    | email    | required, string       |
| password | password | required, string       |
| remember | checkbox | optional               |

## Security Rules

- Passwords are hashed using Laravel's default hasher (bcrypt)
- CSRF protection on all POST requests
- Rate limiting: 5 login attempts per minute per email + IP combination
- Session is regenerated on login to prevent session fixation
- Session is invalidated on logout

## Test Scenarios

### Happy Path
1. Login page renders successfully for guests
2. User can log in with valid email and password
3. After login, user is redirected to dashboard
4. Authenticated user can access the dashboard
5. User can log out
6. After logout, user cannot access protected pages

### Validation
1. Login fails with empty email
2. Login fails with empty password
3. Login fails with wrong password
4. Login fails with non-existent email

### Edge Cases
1. Guest accessing /dashboard is redirected to /login
2. Authenticated user accessing /login is redirected to dashboard
3. Root path (/) redirects to /login
4. Login is rate-limited after 5 failed attempts
