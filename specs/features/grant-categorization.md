# Feature: Grant Categorization

## Overview

Mabes manages a taxonomy of grant categories (tags) and can assign them to grants. Categories help organize and classify grants across the system. The tagging system uses a polymorphic many-to-many relationship.

## Business Rules

- Only Mabes can manage categories (list, create, update)
- Only Mabes can assign/remove categories on grants
- Non-Mabes users are redirected to the dashboard
- Tags are stored in the `tags` table with a `name` field
- Tag-to-grant relationships use a polymorphic `taggables` table
- Multiple tags can be assigned to a single grant
- Tag sync (assign/remove) replaces the full set of tags on a grant

## Actors

| Actor  | Permissions                                      |
|--------|--------------------------------------------------|
| Mabes  | CRUD categories, assign/remove tags on grants    |
| Others | No access (redirected to dashboard)              |

## Routes

| Method | Path                           | Name                 | Description                | Auth  | Role  |
|--------|--------------------------------|----------------------|----------------------------|-------|-------|
| GET    | /tag                           | tag.index            | List all categories        | Yes   | Mabes |
| POST   | /tag                           | tag.store            | Create new category        | Yes   | Mabes |
| PATCH  | /tag/{id}                      | tag.update           | Update category name       | Yes   | Mabes |
| GET    | /hibah/{id}                    | hibah.show           | View grant detail          | Yes   | Mabes |
| POST   | /hibah/{id}/kategorisasi       | hibah.kategorisasi   | Sync tags on a grant       | Yes   | Mabes |

## Category Management

### Create Category
| Field | Type   | Rules    | Description   |
|-------|--------|----------|---------------|
| name  | string | required | Category name |

### Update Category
| Field | Type   | Rules    | Description       |
|-------|--------|----------|-------------------|
| name  | string | required | New category name |

## Tag Sync on Grant

Assigns a set of tags to a grant, replacing any existing tag assignments.

### Request Fields
| Field  | Type      | Rules    | Description                    |
|--------|-----------|----------|--------------------------------|
| tag_id | integer[] | required | Array of tag IDs to assign     |

### Business Rules
- Sync replaces the full tag set: tags not in the array are removed
- Uses polymorphic relationship: `taggables.taggable_type = Grant`, `taggables.taggable_id = grant.id`
- To remove a tag, send the sync request without that tag's ID in the array

---

## Test Scenarios

### Happy Path — Category Management
1. Mabes can access the category list page — sees categories with `data` prop
2. Mabes creates a new category — record created in `tags` table, redirected to list
3. Mabes updates a category name — record updated, old name replaced, redirected to list

### Happy Path — Tag Assignment
4. Mabes can view grant detail page — sees grant with `hibah` prop
5. Mabes assigns multiple tags to a grant — entries created in `taggables` table
6. Mabes removes a tag from a grant by syncing without that tag ID — entry removed from `taggables`

### Access Control
1. Non-Mabes user accessing category list → redirected to dashboard
2. Non-Mabes user creating a category → redirected to dashboard
3. Non-Mabes user updating a category → redirected to dashboard
4. Non-Mabes user assigning tags to a grant → redirected to dashboard
