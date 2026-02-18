# Feature: Activity Log & Change History

## Overview

Track user actions and entity changes across the system using two complementary mechanisms: ActivityLog (high-level action tracking) and ChangeHistory (field-level state snapshots). Provides audit trail visibility for accountability and compliance.

## Business Rules

- Every significant user action is recorded as an ActivityLog entry
- Every create/update/delete on a trackable model captures full state snapshots in ChangeHistory
- Audit records are NEVER deleted (per deletion policy — `nullOnDelete` on all FKs)
- If a user is soft-deleted, their audit records remain with `user_id` intact; if force-deleted, `user_id` becomes NULL
- ActivityLog stores a human-readable message; ChangeHistory stores machine-readable JSONB state
- Logging must not block the user's action — failures in logging should be caught silently (log to error log, never abort the request)
- All timestamps use `timestampsTz()` and display in `Asia/Jakarta` timezone

## Existing Infrastructure

The following already exist and should be used as-is (modify only as specified in this spec):

| Component | Status | Notes |
|-----------|--------|-------|
| `activity_logs` migration | Exists | No changes needed |
| `change_histories` migration | Exists | No changes needed |
| `ActivityLog` model | Exists | No changes needed |
| `ChangeHistory` model | Needs `getChanges()` method |
| `LogAction` enum | Needs additional cases |
| `HasChangeHistory` trait | Exists | No changes needed |
| `HasChangeHistory` contract | Exists | No changes needed |
| `User` model | Needs `activityLogs()` and `changesMade()` relationships + helper methods |

---

## Component 1: LogAction Enum Expansion

Current cases: `Create`, `Update`, `Delete`

Add these cases to support authentication and workflow actions:

| Case | Value | Description |
|------|-------|-------------|
| `Login` | `LOGIN` | User logged in |
| `Logout` | `LOGOUT` | User logged out |
| `Submit` | `SUBMIT` | Proposal/agreement submitted for review |
| `Review` | `REVIEW` | Assessment result submitted |
| `Verify` | `VERIFY` | Proposal/agreement verified (approved) |
| `Reject` | `REJECT` | Proposal/agreement rejected |
| `RequestRevision` | `REQUEST_REVISION` | Revision requested |

Add a `label(): string` method returning Indonesian labels for UI display:

| Case | Label |
|------|-------|
| Create | Membuat |
| Update | Mengubah |
| Delete | Menghapus |
| Login | Masuk |
| Logout | Keluar |
| Submit | Mengajukan |
| Review | Mengkaji |
| Verify | Memverifikasi |
| Reject | Menolak |
| RequestRevision | Meminta Revisi |

---

## Component 2: User Model — Relationships & Helper Methods

### Relationships

Add to `User` model:

```
activityLogs(): HasMany → ActivityLog (via user_id)
changesMade(): HasMany → ChangeHistory (via user_id)
```

### Helper Methods

Three convenience methods on the User model for recording audit entries:

#### `recordCreation(Model $model, string $reason): void`

1. Create ActivityLog with:
   - `action` → `LogAction::Create`
   - `message` → Generated from model class name and identifier
   - `metadata` → `['model_type' => class, 'model_id' => id]`
2. If `$model` implements `HasChangeHistory`, create ChangeHistory with:
   - `changeable` → the model (morph)
   - `change_reason` → `$reason`
   - `state_before` → `null`
   - `state_after` → model's `attributesToArray()` (excluding hidden attributes)

#### `recordChange(Model $model, string $reason): void`

**Must be called BEFORE `save()`** so dirty attributes are available.

1. Create ActivityLog with:
   - `action` → `LogAction::Update`
   - `message` → Generated from model class name and identifier
   - `metadata` → `['model_type' => class, 'model_id' => id, 'changed_fields' => array of dirty field names]`
2. If `$model` implements `HasChangeHistory`, create ChangeHistory with:
   - `changeable` → the model (morph)
   - `change_reason` → `$reason`
   - `state_before` → model's `getOriginal()` (only dirty fields)
   - `state_after` → model's current dirty values

#### `recordDeletion(Model $model, string $reason): void`

**Must be called BEFORE `delete()`** so the model state is still available.

1. Create ActivityLog with:
   - `action` → `LogAction::Delete`
   - `message` → Generated from model class name and identifier
   - `metadata` → `['model_type' => class, 'model_id' => id]`
2. If `$model` implements `HasChangeHistory`, create ChangeHistory with:
   - `changeable` → the model (morph)
   - `change_reason` → `$reason`
   - `state_before` → model's `attributesToArray()` (excluding hidden attributes)
   - `state_after` → `null`

### Message Generation

Activity log messages follow the pattern: `{Action} {ModelLabel}: {Identifier}`

| Model | Label | Identifier |
|-------|-------|------------|
| Grant | hibah | `nama_hibah` |
| Donor | pemberi hibah | `nama` |
| OrgUnit | unit | `nama_unit` |
| OrgUnitChief | kepala unit | `nama_lengkap` |
| User | pengguna | `name` |

For models without a specific label, use the class base name in lowercase.

---

## Component 3: ChangeHistory — `getChanges()` Method

Add a `getChanges()` method to the `ChangeHistory` model that computes a diff between `state_before` and `state_after`:

**Return type:** `array<string, array{from: mixed, to: mixed}>`

**Logic:**
- If `state_before` is null (creation): return all `state_after` keys with `from: null`
- If `state_after` is null (deletion): return all `state_before` keys with `to: null`
- Otherwise: return only keys where the value differs between before and after

**Example:**
```
state_before: {"nama_hibah": "Hibah A", "nilai_hibah": 1000000}
state_after:  {"nama_hibah": "Hibah B", "nilai_hibah": 1000000}

getChanges(): {"nama_hibah": {"from": "Hibah A", "to": "Hibah B"}}
```

---

## Component 4: Authentication Logging

### Login

After successful authentication, record:
- `action` → `LogAction::Login`
- `message` → `Pengguna masuk ke sistem`
- `metadata` → `['ip_address' => request ip, 'user_agent' => request user agent]`

**Integration point:** Fortify's `authenticated` method in `LoginResponse` or via Laravel's `Login` event listener.

### Logout

Before session invalidation, record:
- `action` → `LogAction::Logout`
- `message` → `Pengguna keluar dari sistem`
- `metadata` → `null`

**Integration point:** The existing `Logout` Livewire action (`app/Livewire/Actions/Logout.php`).

---

## Component 5: Trackable Models

Models that implement `HasChangeHistory` (change tracking via state snapshots):

| Model | Priority | Reason |
|-------|----------|--------|
| Grant | Now | Core business entity, tracks all field changes |
| Donor | Now | Reference data, tracks edits |
| OrgUnit | Later | Rarely changes, lower priority |
| OrgUnitChief | Later | Track leadership changes |

**"Now" models** should be implemented with this spec. **"Later" models** are documented for future work.

### What to Exclude from State Snapshots

These fields should be excluded from `state_before` / `state_after` to avoid noise:

- `id` / primary keys
- `created_at`, `updated_at`, `deleted_at` timestamps
- `password`, `remember_token`, `two_factor_secret`, `two_factor_recovery_codes` (sensitive)

Use each model's `$hidden` array plus a configurable exclusion list.

---

## Component 6: Repository Integration Points

Activity logging calls should be placed in **repositories** (not Livewire components or models), since repositories are the business logic layer.

### Grant Repository Actions to Log

| Repository Method | LogAction | ChangeHistory? | Notes |
|-------------------|-----------|----------------|-------|
| Create grant | `Create` | Yes — full state | |
| Update grant fields | `Update` | Yes — dirty fields only | |
| Delete grant (soft) | `Delete` | Yes — full state before | |
| Submit to Polda | `Submit` | No — status tracked via GrantStatusHistory | Log activity only |

### Review Repository Actions to Log

| Repository Method | LogAction | ChangeHistory? | Notes |
|-------------------|-----------|----------------|-------|
| Start review | `Review` | No | Activity only — assessment records track details |
| Submit assessment result | `Review` | No | Activity only |
| Auto-resolve: verify | `Verify` | No | Activity only |
| Auto-resolve: reject | `Reject` | No | Activity only |
| Auto-resolve: revision | `RequestRevision` | No | Activity only |

### Donor Repository Actions to Log

| Repository Method | LogAction | ChangeHistory? | Notes |
|-------------------|-----------|----------------|-------|
| Create donor | `Create` | Yes | |
| Update donor | `Update` | Yes | |
| Delete donor (soft) | `Delete` | Yes | |

---

## Component 7: Activity Log Index Page (Mabes Only)

### Access Control

- Only Mabes users can view the activity log index
- Middleware: `mabes`

### Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /activity-log | activity-log.index | View system activity log |

### Component: `ActivityLog\Index`

Uses `WithPagination`.

### Display

- Paginated list (25 per page)
- Each row shows:
  - Timestamp (Asia/Jakarta, format: `d M Y H:i`)
  - User name (or "Sistem" if `user_id` is null)
  - Action badge (color-coded by `LogAction`)
  - Message
- Default sort: newest first (`created_at DESC`)

### Filters

| Filter | Type | Options |
|--------|------|---------|
| Action | Select | All `LogAction` cases |
| User | Text input | Search by user name |
| Date range | Date pickers | From / To |

### Action Badge Colors

| Action | Color | Variant |
|--------|-------|---------|
| Create | green | `accent` |
| Update | blue | `accent` |
| Delete | red | `accent` |
| Login | zinc | `subtle` |
| Logout | zinc | `subtle` |
| Submit | indigo | `accent` |
| Review | amber | `accent` |
| Verify | emerald | `accent` |
| Reject | rose | `accent` |
| RequestRevision | orange | `accent` |

---

## Component 8: Grant Change History Tab

### Location

Add a "Riwayat Perubahan" tab on the grant detail page, visible to all authenticated users who can view the grant.

### Display

- Chronological list (newest first) of ChangeHistory entries for the grant
- Each entry shows:
  - Timestamp (Asia/Jakarta, format: `d M Y H:i`)
  - User name (or "Sistem" if null)
  - Change reason
  - Expandable diff view showing changed fields via `getChanges()`
    - Field name (use Indonesian column names as-is — they are the domain language)
    - Old value → New value
- If no change history exists, show empty state message: "Belum ada riwayat perubahan"

### No Pagination

Change history per grant is expected to be small (< 50 entries). Render all entries without pagination.

---

## Relationship to GrantStatusHistory

`GrantStatusHistory` (existing) and `ChangeHistory` (this spec) serve different purposes:

| Aspect | GrantStatusHistory | ChangeHistory |
|--------|-------------------|---------------|
| Scope | Grant status transitions only | Any field change on any trackable model |
| Trigger | Status workflow actions | Create/update/delete operations |
| Storage | Dedicated table with status enums | Polymorphic with JSONB snapshots |
| Files | Can have attached files (e.g., revision letter) | No file attachments |

**They do NOT overlap.** GrantStatusHistory continues to track status workflow transitions. ChangeHistory tracks data field modifications (e.g., grant name changed, budget updated).

---

## Test Scenarios

### Happy Path — User Helper Methods
1. `recordCreation` creates both ActivityLog and ChangeHistory for a HasChangeHistory model
2. `recordCreation` creates only ActivityLog for a model without HasChangeHistory
3. `recordChange` captures dirty fields in state_before/state_after (called before save)
4. `recordChange` creates ActivityLog with changed field names in metadata
5. `recordDeletion` captures full state in state_before with null state_after
6. `recordDeletion` creates ActivityLog with Delete action

### Happy Path — ChangeHistory
7. `getChanges()` on creation returns all fields with `from: null`
8. `getChanges()` on deletion returns all fields with `to: null`
9. `getChanges()` on update returns only changed fields with from/to values
10. `getChanges()` excludes unchanged fields

### Happy Path — Authentication Logging
11. Successful login creates ActivityLog with Login action and IP/user-agent metadata
12. Logout creates ActivityLog with Logout action

### Happy Path — Grant Logging
13. Creating a grant records ActivityLog (Create) and ChangeHistory (full state)
14. Updating grant fields records ActivityLog (Update) and ChangeHistory (dirty fields only)
15. Soft-deleting a grant records ActivityLog (Delete) and ChangeHistory (full state before)
16. Submitting grant to Polda records ActivityLog (Submit) without ChangeHistory

### Happy Path — Donor Logging
17. Creating a donor records ActivityLog (Create) and ChangeHistory
18. Updating a donor records ActivityLog (Update) and ChangeHistory

### Happy Path — Review Logging
19. Starting a review records ActivityLog (Review)
20. Submitting assessment result records ActivityLog (Review)
21. Auto-resolve verify records ActivityLog (Verify)
22. Auto-resolve reject records ActivityLog (Reject)
23. Auto-resolve revision records ActivityLog (RequestRevision)

### Happy Path — Activity Log Index (Mabes)
24. Mabes user sees paginated activity logs (newest first)
25. Filtering by action returns only matching entries
26. Filtering by user name returns only matching entries
27. Filtering by date range returns only entries within range
28. User name shows "Sistem" when user_id is null

### Happy Path — Grant Change History Tab
29. Grant detail page shows change history tab with entries
30. Each entry displays timestamp, user, reason, and expandable diff
31. Empty state shown when no change history exists

### Validation
32. `recordChange` with no dirty attributes does not create ChangeHistory (only ActivityLog)
33. Sensitive fields (password, tokens) are excluded from state snapshots

### Access Control
34. Non-Mabes users cannot access activity log index → redirected
35. Grant change history tab respects existing grant detail access control

### Edge Cases
36. Logging failure does not abort the user's action (graceful error handling)
37. ActivityLog with null user_id (force-deleted user) displays correctly
38. ChangeHistory for a soft-deleted model is still accessible
39. Multiple rapid changes to the same model each create separate ChangeHistory entries
