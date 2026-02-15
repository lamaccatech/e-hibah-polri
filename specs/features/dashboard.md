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

| Method | Path                          | Name                          | Description                         | Auth |
|--------|-------------------------------|-------------------------------|-------------------------------------|------|
| GET    | /dashboard                    | dashboard                     | Role-specific dashboard             | Yes  |
| GET    | /hibah-langsung/pilih-tahapan | hibah.lansung.pilih-tahapan   | Direct grant stage selection        | Yes  |
| GET    | /hibah-terencana              | hibah.terencana               | Planned grant info page             | Yes  |
| GET    | /hibah-langsung/usulan/create | hibah.lansung.usulan.create   | New proposal form (activity name)   | Yes  |

## Satker Dashboard

Satker sees options to create new grants and manage existing ones.

### Displayed Elements
- Two grant type options: Direct Grant (Hibah Langsung) and Planned Grant (Hibah Terencana)
- When selecting Direct Grant → stage selection page (Proposal or Agreement)
- When selecting Planned Grant → planned grant information page
- When selecting "Input Proposal" → proposal creation form (activity name entry)

## Polda Dashboard

Polda sees grants pending review and aggregate statistics.

### Displayed Elements
- Pending grants that need Polda review (`data` prop)
- Statistical overview (`stats` prop)

## Mabes Dashboard

Mabes sees incoming proposals and statistics for system oversight.

### Displayed Elements
- Incoming proposals awaiting Mabes review (`usulan_masuk` prop)
- Statistical overview

---

## Test Scenarios

### Happy Path
1. Satker sees dashboard with 2 grant type options after login
2. Satker selects Direct Grant → sees stage selection page (Proposal / Agreement)
3. Satker selects Planned Grant → sees planned grant information page
4. Satker selects "Input Proposal" → sees activity name entry form
5. Polda sees dashboard with pending grants and statistics after login
6. Mabes sees dashboard with incoming proposals and statistics after login

### Access Control
1. Each role sees only their role-specific dashboard component
2. Dashboard requires authentication (unauthenticated users redirected to login)
