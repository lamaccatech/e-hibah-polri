# Feature: Notifications

## Overview

The system sends database notifications to relevant users when key status changes occur in the grant lifecycle. Users view notifications via a bell icon component in the application header. Notifications provide quick navigation to the relevant page.

## Business Rules

- All notifications use the `database` channel only (no email/SMS)
- Notifications are sent to the Satker user (grant owner) or the reviewer
- Each notification stores grant context (`grant_id`, `grant_name`) plus event-specific data
- Users see up to 10 most recent notifications in the bell dropdown
- Notifications can be marked as read individually or all at once
- Clicking a notification navigates to the relevant page based on notification type

## Notification Classes

### Planning Notifications

| Class | Recipient | Trigger | Extra Data |
|-------|-----------|---------|------------|
| `PlanningSubmittedNotification` | Polda (parent unit) | Satker submits planning to Polda | `unit_name` |
| `PlanningRejectedNotification` | Satker (grant owner) | Polda or Mabes rejects planning | `rejected_by` (Polda/Mabes) |
| `PlanningRevisionRequestedNotification` | Satker (grant owner) | Polda or Mabes requests revision | `revision_requested_by` |
| `PlanningNumberIssuedNotification` | Satker (grant owner) | Mabes verifies planning, number issued | `planning_number` |

### Agreement Notifications

| Class | Recipient | Trigger | Extra Data |
|-------|-----------|---------|------------|
| `AgreementSubmittedNotification` | Polda (parent unit) | Satker submits agreement to Polda | `unit_name` |
| `AgreementRejectedNotification` | Satker (grant owner) | Polda or Mabes rejects agreement | `rejected_by` |
| `AgreementRevisionRequestedNotification` | Satker (grant owner) | Polda or Mabes requests revision | `revision_requested_by` |
| `AgreementNumberIssuedNotification` | Satker (grant owner) | Mabes verifies agreement, number issued | `agreement_number` |

## Common Data Schema

All notifications store:

```json
{
  "grant_id": 123,
  "grant_name": "Hibah XYZ",
  // Plus one of:
  "unit_name": "...",           // submitted notifications
  "rejected_by": "Polda",      // rejection notifications
  "revision_requested_by": "Polda", // revision notifications
  "planning_number": "...",     // planning number issued
  "agreement_number": "..."     // agreement number issued
}
```

## Component: `NotificationBell`

Rendered in the app layout header for all authenticated users.

### Actions

| Action | Description |
|--------|-------------|
| `markAsRead(notificationId)` | Marks a single notification as read |
| `markAllAsRead()` | Marks all unread notifications as read |

### URL Routing Logic

The `getUrl()` method determines where clicking a notification navigates, based on the presence of specific data keys:

| Priority | Data Key Present | Destination Route |
|----------|-----------------|-------------------|
| 1 | `planning_number` | `grant-planning.index` |
| 2 | `rejected_by` | `grant-detail.show` (with `grant_id`) |
| 3 | `revision_requested_by` | `grant-planning.edit` (with `grant_id`) |
| 4 | `grant_id` (catch-all) | `grant-review.index` |
| — | None | `null` |

### Known Limitation

The current routing logic does not distinguish between planning and agreement notifications for rejection/revision cases. Agreement rejection and revision notifications will route to planning pages instead of agreement pages. This should be addressed in a future update.

### Display

- Shows unread count badge
- Lists up to 10 most recent notifications (read and unread)
- Empty state message when no notifications exist

## Notification Dispatch Points

| Repository | Status Outcome | Notification Sent |
|------------|---------------|-------------------|
| `GrantPlanningRepository` | Submit to Polda | `PlanningSubmittedNotification` → Polda |
| `GrantReviewRepository` | Polda rejects | `PlanningRejectedNotification` → Satker |
| `GrantReviewRepository` | Polda requests revision | `PlanningRevisionRequestedNotification` → Satker |
| `MabesGrantReviewRepository` | Mabes rejects | `PlanningRejectedNotification` → Satker |
| `MabesGrantReviewRepository` | Mabes requests revision | `PlanningRevisionRequestedNotification` → Satker |
| `MabesGrantReviewRepository` | Mabes verifies (number issued) | `PlanningNumberIssuedNotification` → Satker |
| `GrantAgreementRepository` | Submit to Polda | `AgreementSubmittedNotification` → Polda |
| `AgreementReviewRepository` | Polda rejects | `AgreementRejectedNotification` → Satker |
| `AgreementReviewRepository` | Polda requests revision | `AgreementRevisionRequestedNotification` → Satker |
| `MabesAgreementReviewRepository` | Mabes rejects | `AgreementRejectedNotification` → Satker |
| `MabesAgreementReviewRepository` | Mabes requests revision | `AgreementRevisionRequestedNotification` → Satker |
| `MabesAgreementReviewRepository` | Mabes verifies (number issued) | `AgreementNumberIssuedNotification` → Satker |

## Test Scenarios

### Happy Path — Bell Component
1. Bell renders with zero unread count when no notifications
2. Bell shows unread count after receiving notifications
3. Bell displays notification content (grant name, unit name)
4. User can mark a single notification as read
5. User can mark all notifications as read

### Happy Path — Notification Routing
6. Planning number issued notification routes to grant planning index
7. Rejection notification routes to grant detail page
8. Revision requested notification routes to grant planning edit page
9. Submitted notification (catch-all with `grant_id`) routes to grant review index

### Happy Path — Planning Notifications
10. `PlanningSubmittedNotification` sent to Polda when Satker submits
11. `PlanningRejectedNotification` sent to Satker when Polda rejects
12. `PlanningRevisionRequestedNotification` sent to Satker when Polda requests revision
13. `PlanningRejectedNotification` sent to Satker when Mabes rejects
14. `PlanningRevisionRequestedNotification` sent to Satker when Mabes requests revision
15. `PlanningNumberIssuedNotification` sent to Satker when Mabes verifies

### Happy Path — Agreement Notifications
16. `AgreementSubmittedNotification` sent to Polda when Satker submits
17. `AgreementRejectedNotification` sent to Satker when Polda rejects
18. `AgreementRevisionRequestedNotification` sent to Satker when Polda requests revision
19. `AgreementRejectedNotification` sent to Satker when Mabes rejects
20. `AgreementRevisionRequestedNotification` sent to Satker when Mabes requests revision
21. `AgreementNumberIssuedNotification` sent to Satker when Mabes verifies
