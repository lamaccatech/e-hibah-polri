# Feature: Dashboard

## Overview

Each organizational level sees a role-specific dashboard after login. The dashboard serves as the entry point for all grant-related actions, displaying relevant statistics and pending work items based on the user's role.

## Actors

| Actor  | Dashboard Content                                       |
|--------|---------------------------------------------------------|
| Satker | Grant type selection, start new grants                  |
| Polda  | Pending grants for review, statistics                   |
| Mabes  | Incoming proposals for review, statistics               |

## Routes

| Method | Path       | Name      | Description             | Auth |
|--------|------------|-----------|-------------------------|------|
| GET    | /dashboard | dashboard | Role-specific dashboard | Yes  |

## Satker Dashboard

Satker sees a two-step drill-down to select grant type and stage, implemented as a single page with Alpine.js state.

### Step 1: Grant Type Selection

Two cards side by side:
- **Hibah Langsung (HL)** — clickable, drills down to Step 2
- **Hibah Yang Direncanakan (HDR)** — disabled with "Segera Hadir" badge

### Step 2: Direct Grant Sub-Options

Shown after selecting HL, with a back button to return to Step 1:
- **Input Usulan** — links to `grant-planning.create` (new proposal form)
- **Input Perjanjian** — disabled with "Segera Hadir" badge

## Polda Dashboard

Polda sees grants pending review and aggregate statistics.

### Displayed Elements
- Pending grants that need Polda review
- Statistical overview

## Mabes Dashboard

Mabes sees incoming proposals and statistics for system oversight.

### Displayed Elements
- Incoming proposals awaiting Mabes review
- Statistical overview

---

## Test Scenarios

### Happy Path
1. Satker sees dashboard with 2 grant type options (HL and HDR) after login
2. HDR card is disabled with "Segera Hadir" badge
3. Satker clicks HL → sees sub-options (Input Usulan / Input Perjanjian)
4. Back button returns to grant type selection
5. Satker clicks "Input Usulan" → navigates to grant planning create form
6. "Input Perjanjian" card is disabled with "Segera Hadir" badge
7. Polda sees dashboard with pending grants and statistics after login
8. Mabes sees dashboard with incoming proposals and statistics after login

### Access Control
1. Each role sees only their role-specific dashboard component
2. Dashboard requires authentication (unauthenticated users redirected to login)
