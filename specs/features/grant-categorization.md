# Feature: Grant Categorization

## Overview

Mabes manages a taxonomy of grant categories (tags) and assigns them to grants. Categories help organize and classify grants across the system. Uses a polymorphic many-to-many relationship via the `taggables` table.

## Business Rules

- Only Mabes can manage categories (list, create, update)
- Only Mabes can assign/remove categories on grants (via grant detail page)
- Non-Mabes users are redirected to the dashboard
- Tags are stored in the `tags` table with a `name` field
- Tag-to-grant relationships use a polymorphic `taggables` table
- Multiple tags can be assigned to a single grant
- Tag sync replaces the full set of tags on a grant

## Actors

| Actor  | Permissions                                      |
|--------|--------------------------------------------------|
| Mabes  | CRUD categories, assign/remove tags on grants    |
| Others | No access (redirected to dashboard)              |

## Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /tag | tag.index | List & manage categories |

Tag assignment on grants is done inline on the grant detail page (no separate route).

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

## Tag Assignment on Grant

On the grant detail page (Grant Info tab), Mabes sees a "Kategori" section with:
- Current tags displayed as badges
- A combobox/select to add tags
- Click badge to remove tag
- Uses `grant->tags()->sync()` under the hood

## Test Scenarios

### Happy Path — Category Management
1. Mabes can access the category list page
2. Mabes creates a new category — record created in `tags` table
3. Mabes updates a category name — record updated

### Happy Path — Tag Assignment
4. Mabes assigns tags to a grant on detail page — entries created in `taggables`
5. Mabes removes a tag from a grant — entry removed from `taggables`

### Validation
6. Create fails with empty name
7. Create fails with duplicate name
8. Update fails with duplicate name

### Access Control
9. Non-Mabes user accessing category list → redirected to dashboard
