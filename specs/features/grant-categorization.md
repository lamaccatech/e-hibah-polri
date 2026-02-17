# Feature: Grant Categorization

## Overview

Mabes manages a taxonomy of grant categories (tags) and assigns them to grants. Categories help organize and classify grants across the system. Uses a polymorphic many-to-many relationship via the `taggables` table.

## Business Rules

- Only Mabes can manage categories (list, create, update)
- Only Mabes can assign a category to a grant
- Each grant can have at most one tag assigned
- Non-Mabes users are redirected to the dashboard
- Tags are stored in the `tags` table with a `name` field
- Tag-to-grant relationships use a polymorphic `taggables` table

## Actors

| Actor  | Permissions                                      |
|--------|--------------------------------------------------|
| Mabes  | CRUD categories, assign tag to grants            |
| Others | No access (redirected to dashboard)              |

## Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /tag | tag.index | List & manage categories |

Tag assignment is done inline on the Mabes agreement review index page (no separate route).

## Category Management Page

**Component:** `TagManagement\Index`

### Features
- List all tags in a table
- Inline create: text input + button at top
- Inline edit: click tag name to edit in-place
- No delete (tags may be in use; keep simple)

### Create
| Field | Type | Rules |
|-------|------|-------|
| name | string | required, unique |

### Update
| Field | Type | Rules |
|-------|------|-------|
| name | string | required, unique (except current) |

## Tag Assignment on Mabes Agreement Review

**Component:** `MabesAgreementReview\Index`

On the Mabes agreement review index page, each grant row has a tag icon button next to the eye (view) button. Clicking it opens a dropdown showing all available tags as radio items. The currently assigned tag (if any) is shown as selected.

### UI
- Tag icon button (`icon="tag"`) in the action column, next to existing eye icon
- `flux:dropdown` with `flux:menu.radio.group` listing all tags
- Selected state for the currently assigned tag
- A "clear" option to remove the assigned tag
- Selecting a tag calls `assignTag(grantId, tagId)` on the component
- Selecting the clear option calls `assignTag(grantId, null)`

### Constraint
- One tag per grant (uses `sync([$tagId])` or `sync([])`)

## Test Scenarios

### Happy Path — Category Management
1. Mabes can access the category list page
2. Mabes creates a new category — record created in `tags` table
3. Mabes updates a category name — record updated

### Happy Path — Tag Assignment
4. Mabes assigns a tag to a grant via the agreement review page
5. Mabes changes the assigned tag on a grant (replaces previous)
6. Mabes removes the assigned tag from a grant

### Validation
7. Create fails with empty name
8. Create fails with duplicate name
9. Update fails with duplicate name

### Access Control
10. Non-Mabes user accessing category list → redirected to dashboard
