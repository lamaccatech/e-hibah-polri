# Feature: Post-Approval Flow

## Overview

After Mabes issues an agreement number (`AgreementNumberIssued`), the Satker completes two final steps: uploading the signed agreement document and submitting data to SEHATI (Kemenkeu). These are sequential — signed agreement first, then SEHATI submission.

## Business Rules

- Only the owning Satker can perform post-approval steps
- Steps are sequential: upload signed agreement → submit SEHATI data
- Signed agreement upload requires a PDF file (`FileType::Agreement`)
- SEHATI submission creates a `GrantFinanceMinistrySubmission` record (1:1 with grant)
- After SEHATI submission, the grant lifecycle is complete

## Actors

| Actor  | Permissions                                    |
|--------|------------------------------------------------|
| Satker | Upload signed agreement, submit SEHATI data    |
| Others | View only (via grant detail)                   |

## Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /grant-agreement/{grant}/upload-signed | grant-agreement.upload-signed | Upload signed agreement form |
| GET | /grant-agreement/{grant}/sehati | grant-agreement.sehati | SEHATI submission form |

## Step 1: Upload Signed Agreement

**Component:** `GrantAgreement\UploadSignedAgreement`
**Status transition:** `AgreementNumberIssued` → `UploadingSignedAgreement`

### Fields

| Field | Type | Rules | Description |
|-------|------|-------|-------------|
| signedAgreementFile | file | required, pdf, max:20MB | Signed agreement PDF |

### Save Behavior
- Attach file to `GrantStatusHistory` via `HasFiles`, type `FileType::Agreement`
- Create status history: `status_sesudah = UploadingSignedAgreement`
- Redirect to SEHATI form (Step 2)

## Step 2: SEHATI/Kemenkeu Submission

**Component:** `GrantAgreement\SehatiSubmission`
**Status transition:** `UploadingSignedAgreement` → `SubmittingToFinanceMinistry`

### Fields

| Field | Type | Rules | Description |
|-------|------|-------|-------------|
| grantRecipient | string | required | Penerima hibah |
| fundingSource | string | required | Sumber pembiayaan |
| fundingType | string | required | Jenis pembiayaan |
| withdrawalMethod | string | required | Cara penarikan |
| effectiveDate | date | required | Tanggal efektif |
| withdrawalDeadline | date | required, after:effectiveDate | Tanggal batas penarikan |
| accountClosingDate | date | required, after:withdrawalDeadline | Tanggal penutupan rekening |

### Save Behavior
- Create `GrantFinanceMinistrySubmission` record linked to grant
- Create status history: `status_sesudah = SubmittingToFinanceMinistry`
- Redirect to grant agreement index

## Entry Points

- After Mabes issues agreement number, grant appears in Satker's agreement list with "Upload Naskah Perjanjian" action
- After signed agreement uploaded, "Isi Data SEHATI" action appears

## Grant Detail Display

- Signed agreement file viewable/downloadable in Document History tab
- SEHATI data viewable in Grant Info tab (new section after agreement number)

## Test Scenarios

### Happy Path
1. Satker can access upload signed agreement form when status is `AgreementNumberIssued`
2. Satker uploads signed agreement PDF — file saved, status transitions
3. Satker can access SEHATI form when status is `UploadingSignedAgreement`
4. Satker fills all SEHATI fields — record created, status transitions to `SubmittingToFinanceMinistry`

### Validation
5. Upload fails without file
6. Upload fails with non-PDF file
7. SEHATI submission fails with missing required fields
8. SEHATI fails when `withdrawalDeadline` is before `effectiveDate`
9. SEHATI fails when `accountClosingDate` is before `withdrawalDeadline`

### Access Control
10. Non-owner Satker cannot access post-approval forms
11. Cannot upload signed agreement unless status is `AgreementNumberIssued`
12. Cannot submit SEHATI unless status is `UploadingSignedAgreement`
