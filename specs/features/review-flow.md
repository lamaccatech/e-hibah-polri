# Feature: Review Flow (Polda & Mabes)

## Overview

Polda and Mabes review grants submitted by subordinate units. Reviews follow an assessment-based workflow: the reviewer evaluates each assessment aspect and submits a result (Fulfilled, Revision, Rejected). The system automatically determines the overall outcome when all aspects are evaluated.

## Business Rules

- Polda reviews grants from their child Satker units
- Mabes reviews grants that Polda has verified
- Starting a review transitions the grant to a "reviewing" status and creates assessment records
- Each assessment has aspects defined by `AssessmentAspect` enum (Technical, Economic, Political, Strategic)
- Reviewers evaluate each aspect independently with a result: Fulfilled, Revision, or Rejected
- Remarks are required unless result is Fulfilled
- Once all aspects have results, the system auto-resolves the overall outcome
- An aspect cannot be re-evaluated once a result is submitted
- Resolution priority: Rejected > Revision > Fulfilled (if any aspect is Rejected → rejected; if any is Revision → revision requested; else → verified)
- On verification at Mabes level, a grant number is automatically issued

## Actors

| Actor | Planning | Agreement |
|-------|----------|-----------|
| Polda | Review Satker's planning proposals | Review Satker's agreements |
| Mabes | Review Polda-verified planning proposals | Review Polda-verified agreements |

## Review Modules

There are 4 parallel review modules following the same pattern:

| Module | Actor | Stage | Middleware |
|--------|-------|-------|------------|
| Grant Review | Polda | Planning | `polda` |
| Agreement Review | Polda | Agreement | `polda` |
| Mabes Grant Review | Mabes | Planning | `mabes` |
| Mabes Agreement Review | Mabes | Agreement | `mabes` |

## Routes

### Polda — Planning Review

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /grant-review | grant-review.index | List planning proposals submitted to Polda |
| GET | /grant-review/{grant}/review | grant-review.review | Review a specific planning proposal |

### Polda — Agreement Review

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /agreement-review | agreement-review.index | List agreements submitted to Polda |
| GET | /agreement-review/{grant}/review | agreement-review.review | Review a specific agreement |

### Mabes — Planning Review

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /mabes-grant-review | mabes-grant-review.index | List Polda-verified planning proposals |
| GET | /mabes-grant-review/{grant}/review | mabes-grant-review.review | Review a specific planning proposal |

### Mabes — Agreement Review

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /mabes-agreement-review | mabes-agreement-review.index | List Polda-verified agreements |
| GET | /mabes-agreement-review/{grant}/review | mabes-agreement-review.review | Review a specific agreement |

## Index Page Pattern

All 4 index pages follow the same pattern:

### Components
- `{Module}\Index` (uses `WithPagination`)

### Display
- Paginated list of grants (15 per page) with donor, org unit, and status info
- Each grant shows current status derived from status history
- Action buttons per grant:
  - **Mulai Kajian** — when `canStartReview` is true (grant is in submittable status)
  - **Lanjutkan Kajian** — when `isUnderReview` is true (review in progress)
  - **Lihat** — link to grant detail page

### Start Review Flow
1. User clicks "Mulai Kajian" → confirmation modal appears
2. User confirms → `startReview()` Livewire action called
3. System creates status history entry (e.g., `PoldaReviewingPlanning`)
4. System creates `GrantAssessment` records for each `AssessmentAspect`
5. User is redirected to the review page

### Mabes Agreement Review — Extra Feature
- Tag assignment dropdown on each grant row (see `grant-categorization.md`)
- Uses `assignTag(grantId, tagId)` Livewire action

## Review Page Pattern

All 4 review pages follow the same pattern:

### Components
- `{Module}\Review`

### Mount Authorization
- Polda modules: verify grant belongs to a child Satker AND is under review
- Mabes modules: verify grant is under review

### Display
- Satker's original assessment contents (from the assessment creation step)
- For Mabes modules: also shows Polda's assessment results
- Each aspect shows: title, Satker's content, and a button to submit result (if not yet evaluated)

### Submit Result Modal
- Opens via `openResultModal(assessmentId, aspectLabel)`
- Fields:
  - `result`: required, select from `AssessmentResult` enum values (Fulfilled, Revision, Rejected)
  - `remarks`: required unless result is Fulfilled
- Submits via `submitResult()` Livewire action
- If the evaluated aspect was the last pending one, auto-resolves and redirects to index

## Auto-Resolution Logic

When all aspects for a review have results, the repository's `resolveIfComplete()` method runs:

```
IF any aspect = Rejected → overall status = Rejected
ELSE IF any aspect = Revision → overall status = Revision Requested
ELSE → overall status = Verified
```

### Planning Resolution Outcomes

| Reviewer | Result | New Status | Notification | Next Step |
|----------|--------|------------|-------------|-----------|
| Polda | Rejected | `PoldaRejectedPlanning` | `PlanningRejectedNotification` → Satker | — |
| Polda | Revision | `PoldaRequestedPlanningRevision` | `PlanningRevisionRequestedNotification` → Satker | Satker revises |
| Polda | Verified | `PoldaVerifiedPlanning` | — | Appears in Mabes index |
| Mabes | Rejected | `MabesRejectedPlanning` | `PlanningRejectedNotification` → Satker | — |
| Mabes | Revision | `MabesRequestedPlanningRevision` | `PlanningRevisionRequestedNotification` → Satker | Satker revises |
| Mabes | Verified | `MabesVerifiedPlanning` → `PlanningNumberIssued` | `PlanningNumberIssuedNotification` → Satker | Planning complete |

### Agreement Resolution Outcomes

| Reviewer | Result | New Status | Notification | Next Step |
|----------|--------|------------|-------------|-----------|
| Polda | Rejected | `PoldaRejectedAgreement` | `AgreementRejectedNotification` → Satker | — |
| Polda | Revision | `PoldaRequestedAgreementRevision` | `AgreementRevisionRequestedNotification` → Satker | Satker revises |
| Polda | Verified | `PoldaVerifiedAgreement` | — | Appears in Mabes index |
| Mabes | Rejected | `MabesRejectedAgreement` | `AgreementRejectedNotification` → Satker | — |
| Mabes | Revision | `MabesRequestedAgreementRevision` | `AgreementRevisionRequestedNotification` → Satker | Satker revises |
| Mabes | Verified | `MabesVerifiedAgreement` → `AgreementNumberIssued` | `AgreementNumberIssuedNotification` → Satker | Post-approval flow |

## Number Issuance

When Mabes verifies (all aspects Fulfilled), a grant number is automatically issued:

- **Planning:** `MabesGrantReviewRepository::issuePlanningNumber()` via `GrantNumberingRepository`
- **Agreement:** `MabesAgreementReviewRepository::issueAgreementNumber()` via `GrantNumberingRepository`

## Test Scenarios

### Happy Path — Index
1. Polda sees planning proposals submitted by child Satker units
2. Polda sees agreements submitted by child Satker units
3. Mabes sees Polda-verified planning proposals
4. Mabes sees Polda-verified agreements
5. Grants show correct action buttons based on status

### Happy Path — Start Review
6. Polda starts planning review → status transitions, assessment records created
7. Polda starts agreement review → status transitions, assessment records created
8. Mabes starts planning review → status transitions, assessment records created
9. Mabes starts agreement review → status transitions, assessment records created

### Happy Path — Submit Results
10. Reviewer submits Fulfilled result for an aspect → result saved
11. Reviewer submits Revision result with remarks → result saved
12. Reviewer submits Rejected result with remarks → result saved
13. After all aspects evaluated, auto-resolution triggers correct outcome

### Happy Path — Resolution
14. All aspects Fulfilled → grant verified, number issued (Mabes)
15. Any aspect Rejected → grant rejected, notification sent
16. Any aspect Revision → revision requested, notification sent
17. Polda verification makes grant appear in Mabes review index

### Validation
18. Result is required
19. Remarks required when result is not Fulfilled
20. Cannot re-submit result for an already-evaluated aspect → 422

### Access Control
21. Polda cannot review grants from non-child Satker → 403
22. Mabes cannot review non-Polda-verified grants → 403
23. Cannot access review page when grant is not under review → 403
24. Non-Polda user cannot access Polda review pages → redirected
25. Non-Mabes user cannot access Mabes review pages → redirected
