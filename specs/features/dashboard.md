# Feature: Dashboard

## Overview

Each organizational level sees a role-specific dashboard after login. The dashboard serves as the entry point for all grant-related actions, displaying relevant statistics and pending work items based on the user's role.

## Actors

| Actor  | Dashboard Content                                       |
|--------|---------------------------------------------------------|
| Satker | Grant type selection, start new grants                  |
| Polda  | Stats cards (planning + agreement), inbox               |
| Mabes  | Stats cards (planning + agreement), realization charts   |

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
- **Input Perjanjian** — links to `grant-agreement.create`

## Polda Dashboard

Polda sees grant statistics and an inbox of unprocessed grants. All counts are scoped to the Polda's child satkers only.

### Section 1: Usulan (Planning)

Four stats cards in a 4-column grid:

| Card | Label | Count Logic |
|------|-------|-------------|
| 1 | Usulan Dibuat | All planning grants that have reached `PlanningSubmittedToPolda` or beyond |
| 2 | Usulan Belum Diproses | Planning grants at `PlanningSubmittedToPolda` or `PlanningRevisionResubmitted` (pending Polda action) |
| 3 | Usulan Diproses | Planning grants at `PoldaReviewingPlanning` (under Polda review, not yet submitted to Mabes) |
| 4 | Usulan Ditolak | Planning grants at `PoldaRejectedPlanning` |

### Section 2: Perjanjian (Agreement)

Four stats cards in a 4-column grid, same pattern as planning:

| Card | Label | Count Logic |
|------|-------|-------------|
| 1 | Perjanjian Dibuat | All agreement grants that have reached `AgreementSubmittedToPolda` or beyond |
| 2 | Perjanjian Belum Diproses | Agreement grants at `AgreementSubmittedToPolda` or `AgreementRevisionResubmitted` |
| 3 | Perjanjian Diproses | Agreement grants at `PoldaReviewingAgreement` |
| 4 | Perjanjian Ditolak | Agreement grants at `PoldaRejectedAgreement` |

### Section 3: Inbox

Table with pagination showing grants that are not yet processed by Polda (pending action).

**Scope:** Planning grants at `PlanningSubmittedToPolda` or `PlanningRevisionResubmitted`, and agreement grants at `AgreementSubmittedToPolda` or `AgreementRevisionResubmitted` — from child satkers.

**Table Columns:**

| Column | Source |
|--------|--------|
| Nama Kegiatan | `grant.nama_hibah` |
| Satuan Kerja | `grant.orgUnit.nama_unit` |
| Tahapan | Planning / Agreement |
| Status | Latest status label |
| Aksi | Link to review page |

Paginated, 10 per page.

## Mabes Dashboard

Mabes sees system-wide statistics, realization charts, and an inbox. All counts/values are system-wide (all satkers).

### Section 1: Usulan (Planning)

Four stats cards in a 4-column grid (same pattern as Polda, but system-wide scope):

| Card | Label | Count Logic |
|------|-------|-------------|
| 1 | Usulan Dibuat | All planning grants that have reached `PlanningSubmittedToPolda` or beyond |
| 2 | Usulan Belum Diproses | Planning grants at `PoldaVerifiedPlanning` (pending Mabes action) |
| 3 | Usulan Diproses | Planning grants at `MabesReviewingPlanning` |
| 4 | Usulan Ditolak | Planning grants at `MabesRejectedPlanning` |

### Section 2: Perjanjian (Agreement)

Four stats cards in a 4-column grid:

| Card | Label | Count Logic |
|------|-------|-------------|
| 1 | Perjanjian Dibuat | All agreement grants that have reached `AgreementSubmittedToPolda` or beyond |
| 2 | Perjanjian Belum Diproses | Agreement grants at `PoldaVerifiedAgreement` |
| 3 | Perjanjian Diproses | Agreement grants at `MabesReviewingAgreement` |
| 4 | Perjanjian Ditolak | Agreement grants at `MabesRejectedAgreement` |

### Section 3: Realization Pie Charts

Two cards in a 2-column grid, each containing a pie chart showing realization percentage.

#### Definitions

- **Plan value:** SUM of `rencana_anggaran_biaya_hibah.nilai` (budget plan items) for all grants that have reached planning submission or beyond
- **Realization value:** SUM of `rencana_anggaran_biaya_hibah.nilai` for grants that have reached `UploadingSignedAgreement` status (signed agreement document uploaded). This is the completion threshold — not `AgreementNumberIssued`.
- **Percentage:** realization value / plan value

#### Card 1: Hibah Barang / Jasa

Pie chart showing realization vs plan for grants where `bentuk_hibah` contains `BARANG` or `JASA`.

- Grants can have multiple `bentuk_hibah` values (stored as CSV, e.g. `"BARANG,JASA"`)
- A grant qualifies if its `bentuk_hibah` includes `BARANG` or `JASA` (or both)

#### Card 2: Hibah Uang

Pie chart showing realization vs plan for grants where `bentuk_hibah` contains `UANG`.

### Section 4: Area Chart — Plan vs Realization

Full-width area chart comparing plan value and realization value across all grants over time.

- X-axis: time (yearly)
- Y-axis: cumulative value
- Two series: Plan (all submitted grants' budget totals) and Realization (completed grants' budget totals)
- "Completed" = signed agreement document uploaded (`UploadingSignedAgreement`)

---

## Test Scenarios

### Happy Path
1. Satker sees dashboard with 2 grant type options (HL and HDR) after login
2. HDR card is disabled with "Segera Hadir" badge
3. Satker clicks HL → sees sub-options (Input Usulan / Input Perjanjian)
4. Back button returns to grant type selection
5. Satker clicks "Input Usulan" → navigates to grant planning create form
6. Satker clicks "Input Perjanjian" → navigates to grant agreement create form
7. Polda sees planning stats cards with correct counts
8. Polda sees agreement stats cards with correct counts
9. Polda inbox shows only unprocessed grants from child satkers
10. Polda inbox is paginated
11. Mabes sees planning stats cards with correct system-wide counts
12. Mabes sees agreement stats cards with correct system-wide counts
13. Mabes sees Hibah Barang/Jasa pie chart with correct realization percentage
14. Mabes sees Hibah Uang pie chart with correct realization percentage
15. Mabes sees area chart with plan vs realization series

### Access Control
1. Each role sees only their role-specific dashboard component
2. Dashboard requires authentication (unauthenticated users redirected to login)
