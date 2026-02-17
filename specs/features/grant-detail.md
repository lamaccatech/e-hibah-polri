# Feature: Grant Detail

## Overview

A read-only detail page that displays comprehensive information about a grant across multiple tabs. All authenticated users can view grants within their organizational scope. The page also provides entry points for post-approval actions (upload signed agreement, SEHATI submission) and agreement number revision.

## Business Rules

- All authenticated users can view grant details — scoped to organizational hierarchy
- Satker can only view their own grants (`grant.id_satuan_kerja === unit.id_user`)
- Polda can view grants belonging to their child Satker units
- Mabes can view all grants
- Tab content is loaded dynamically based on active tab
- Agreement-stage grants show additional tabs (agreement info, agreement assessment)
- Grants with proposals (`ada_usulan = true`) show a "Lihat Data Usulan" toggle to reveal proposal tabs

## Actors

| Actor  | Permissions                                                |
|--------|------------------------------------------------------------|
| Satker | View own grants, revise agreement number, post-approval    |
| Polda  | View child Satker grants                                   |
| Mabes  | View all grants                                            |

## Routes

| Method | Path                  | Name               | Description        | Auth |
|--------|-----------------------|--------------------|--------------------|------|
| GET    | /grant-detail/{grant} | grant-detail.show | View grant detail  | Yes  |

## Component: `GrantDetail\Show`

### Tabs

| Tab ID               | Label                  | Visibility                                      |
|----------------------|------------------------|--------------------------------------------------|
| grant-info           | Informasi Hibah        | Always visible                                   |
| proposal-info        | Informasi Usulan       | When `hasProposal` and `showProposal` toggled on |
| assessment-info      | Kajian Usulan          | When `hasProposal` and `showProposal` toggled on |
| agreement-info       | Informasi Perjanjian   | When `isAgreementStage`                          |
| agreement-assessment | Kajian Perjanjian      | When `isAgreementStage`                          |
| document-history     | Riwayat Dokumen        | When `hasProposal` and `showProposal` toggled on |

### Tab: Grant Info

- Status history timeline (ordered by ID)
- Uploaded files (non-generated documents, from status history entries)
- SEHATI submission data (if exists)

### Tab: Proposal Info

- Proposal chapters (GrantInformation with `tahapan = Planning`) with contents
- Budget plans (ordered by `nomor_urut`)
- Activity schedules

### Tab: Assessment Info

- Satker's assessment contents (from `CreatingPlanningAssessment` status history)
- Polda's assessment results (from `PoldaReviewingPlanning` status history)
- Mabes's assessment results (from `MabesReviewingPlanning` status history)

### Tab: Agreement Info

- Agreement chapters (GrantInformation with `tahapan = Agreement`) with contents
- Budget plans
- Withdrawal plans (ordered by `nomor_urut`)
- Activity schedules

### Tab: Agreement Assessment

- Satker's agreement assessment contents (from `CreatingAgreementAssessment`)
- Polda's agreement assessment results (from `PoldaReviewingAgreement`)
- Mabes's agreement assessment results (from `MabesReviewingAgreement`)

### Tab: Document History

- Generated documents grouped by type (proposal, assessment, readiness)
- Each type shows list of documents with download links

## Action Buttons (Satker Only)

### Upload Signed Agreement

- Visible when: Satker owns the grant AND `canUploadSignedAgreement` returns true
- Links to `grant-agreement.upload-signed`

### Submit SEHATI

- Visible when: Satker owns the grant AND `canSubmitSehati` returns true
- Links to `grant-agreement.sehati`

### Revise Agreement Number Month

- Visible when ALL conditions met:
  - Satker owns the grant
  - Grant has a status history entry with `status_sesudah = AgreementNumberIssued`
  - Grant has an agreement-stage numbering record
  - The number issuance month is earlier than the current month
  - The numbering year matches the current year
- Livewire action: `reviseAgreementNumberMonth`
- Updates the agreement numbering via `GrantNumberingRepository::reviseAgreementNumberMonth`
- Shows success flash message

## Access Control

Uses `GrantDetailRepository::canView()`:

```
Satker  → grant.id_satuan_kerja === unit.id_user
Polda   → unit has a child where id_user === grant.id_satuan_kerja
Mabes   → always true
```

Returns 403 if `canView` returns false.

## Test Scenarios

### Happy Path
1. Satker can view their own grant's detail page
2. Polda can view a child Satker's grant detail
3. Mabes can view any grant detail
4. Grant Info tab shows status history and uploaded files
5. Toggle "Lihat Data Usulan" reveals proposal tabs for grants with proposals
6. Agreement Info tab shows agreement chapters, budget plans, withdrawal plans
7. Assessment Info tab shows Satker/Polda/Mabes assessment data
8. Document History tab shows generated documents grouped by type

### Agreement Number Revision
9. Satker can revise agreement number month when conditions are met
10. Revision updates the numbering record and refreshes the page
11. Revision button is hidden when conditions are not met (same month, different year, etc.)

### Access Control
12. Satker cannot view another Satker's grant → 403
13. Polda cannot view grants outside their child units → 403
14. Unauthenticated user → redirected to login
