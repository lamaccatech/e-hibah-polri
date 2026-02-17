# Feature: Grant Listing (Polda & Mabes)

## Overview

Polda and Mabes can browse all grants that have been submitted to Polda for review. Once a grant reaches `PlanningSubmittedToPolda` or `AgreementSubmittedToPolda`, it becomes visible to both Polda and Mabes — regardless of current status. This is separate from the review action pages which are task-oriented.

## Visibility Rule

**A grant becomes visible to Polda and Mabes once it has been submitted to Polda.** After that point, the grant remains visible regardless of subsequent status changes (revision, rejection, re-submission, Mabes review, number issued, post-approval).

### Planning Grants
Visible once any of these statuses have occurred:
- `PlanningSubmittedToPolda`
- Any status after this point (revision, Polda/Mabes review, number issued)

### Agreement Grants
Visible once any of these statuses have occurred:
- `AgreementSubmittedToPolda`
- Any status after this point

### Who does NOT see this listing
- Satker — they have their own grant-planning and grant-agreement index pages

## Business Rules

- Polda sees grants from their child satkers only
- Mabes sees grants from all satkers across the system
- Both planning and agreement grants appear in one unified list
- Read-only listing — links to grant detail page for viewing
- Searchable by grant name (`nama_hibah`)
- Paginated (15 per page)

## Actors

| Actor  | Permissions                                     |
|--------|-------------------------------------------------|
| Polda  | View grants from child satkers (post-submission)|
| Mabes  | View all grants system-wide (post-submission)   |
| Satker | No access (uses own index pages)                |

## Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /grant | grant.index | Grant listing for Polda/Mabes |

## Grant Listing Page

**Component:** `GrantListing\Index`

### Table Columns

| Column | Source |
|--------|--------|
| Nama Kegiatan | `grant.nama_hibah` |
| Satuan Kerja | `grant.orgUnit.nama_unit` |
| Pemberi Hibah | `grant.donor.nama` |
| Tahapan | `grant.tahapan` (Planning/Agreement) |
| Status | Latest status label |
| Nilai | `grant.nilai_hibah` |
| Aksi | Link to grant detail |

### Search
- `wire:model.live.debounce.300ms` on `nama_hibah`
- Uses `ilike` for case-insensitive search

### Polda Scope
- Grants where `id_satuan_kerja` belongs to child units of the Polda
- Must have reached `PlanningSubmittedToPolda` or `AgreementSubmittedToPolda` (or beyond)

### Mabes Scope
- All grants system-wide
- Must have reached `PlanningSubmittedToPolda` or `AgreementSubmittedToPolda` (or beyond)

## Test Scenarios

### Happy Path
1. Polda can access grant listing page
2. Mabes can access grant listing page
3. Polda sees only grants from child satkers that have been submitted
4. Mabes sees all grants that have been submitted to any Polda
5. Grants in draft (before submission) are not visible
6. Search filters by grant name

### Access Control
7. Satker redirected to dashboard from grant listing
