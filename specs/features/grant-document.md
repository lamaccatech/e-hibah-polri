# Feature: Grant Document Generation & Download

## Overview

Satker can generate official PDF documents for their grants (proposal, assessment, readiness) and download them. The system also provides download endpoints for previously generated documents and for files attached to status history entries.

## Business Rules

- Only the owning Satker can generate documents for a grant
- Document generation requires an active unit chief (with signature)
- A document date must be provided before generating
- Each generation creates a new `GrantDocument` record (preserving history)
- Generated PDFs are stored as `FileType::GeneratedDocument` attached to `GrantDocument`
- Any authenticated user who can view the grant can download documents and files

## Actors

| Actor  | Permissions                                     |
|--------|-------------------------------------------------|
| Satker | Generate documents for own grants, download     |
| Others | Download only (if grant is viewable)            |

## Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /grant-detail/{grant}/document/{type} | grant-document.generate | Document generation page |
| GET | /grant-detail/{grant}/document/{grantDocument}/download | grant-document.download | Download generated document |
| GET | /grant-detail/{grant}/file/{file}/download | grant-file.download | Download status history file |

## Document Types

Defined in `GrantGeneratedDocumentType` enum:

| Enum Value | Slug | Label | PDF View |
|------------|------|-------|----------|
| `ProposalDocument` | proposal | Naskah Usulan | `pdf.proposal-document` |
| `AssessmentDocument` | assessment | Kajian Usulan | `pdf.assessment-document` |
| `ReadinessDocument` | readiness | Kesiapan Penerimaan Hibah Langsung | `pdf.readiness-document` |

## Component: `GrantDocument\Generate`

### Fields

| Field | Type | Rules | Description |
|-------|------|-------|-------------|
| documentDate | date | required | Date to print on the document |

### Actions

- **Preview** — Toggles inline preview of the PDF content
- **Download** — Generates PDF via Spatie Laravel PDF, persists `GrantDocument` record with attached file, streams PDF to browser

### Generation Flow

1. Validate document date
2. Get document data from `GrantDocumentRepository::getDocumentData()`
3. Render PDF via `Pdf::view($template, $data)->format('a4')`
4. Save to temp file, persist via `GrantDocumentRepository::persistDocument()`
5. Stream download response to browser, clean up temp file

### Document Data Sources

| Type | Data Included |
|------|---------------|
| Proposal | Grant, org unit, chapters (planning stage), budget plans, activity schedules, planning number, chief, date |
| Assessment | Grant, donor, org unit, purpose/objective chapters, budget plans, Satker assessments, chief, date |
| Readiness | Grant, donor, org unit, Satker assessments, Polda/Mabes results, SEHATI data, withdrawal plans, location allocations, chief, date |

## Controller: `DownloadDocumentController`

- Validates `grantDocument` belongs to the `grant`
- Checks `canView` permission via `GrantDetailRepository`
- Downloads the first `FileType::GeneratedDocument` file attached to the `GrantDocument`

## Controller: `DownloadStatusHistoryFileController`

- Checks `canView` permission via `GrantDetailRepository`
- Validates that the `file` belongs to a `GrantStatusHistory` of the given grant
- Downloads the file from storage

## Test Scenarios

### Happy Path
1. Satker can access the document generation page for their grant
2. Satker can preview a document with a valid date
3. Satker can download a generated PDF — file is saved and streamed
4. Document history shows the newly generated document
5. Any user who can view the grant can download a generated document
6. Any user who can view the grant can download a status history file

### Validation
7. Generation fails without a document date
8. Generation fails without an active unit chief → 403
9. Download fails if document does not belong to the grant → 404
10. Download fails if file does not belong to the grant's status history → 404

### Access Control
11. Non-owner Satker cannot access the generation page → 403
12. User who cannot view the grant cannot download documents → 403
13. User who cannot view the grant cannot download status history files → 403
14. Unauthenticated user → redirected to login
