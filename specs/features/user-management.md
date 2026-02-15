# Feature: User Management

## Overview

Mabes (headquarters) provisions and manages user accounts along with their organizational units. There is no self-registration — all user accounts are created exclusively by Mabes. Each user account is tied to an organizational unit (OrgUnit).

## Business Rules

- Only Mabes can access user management pages (list, create, edit)
- Non-Mabes users are redirected to the dashboard when attempting to access user management
- Creating a user also creates an associated organizational unit (OrgUnit) record
- Updating a user can also update their organizational unit data
- Each user maps to exactly one organizational unit via `unit.id_user`

## Actors

| Actor  | Permissions                                 |
|--------|---------------------------------------------|
| Mabes  | Create, read, update user accounts + units  |
| Others | No access (redirected to dashboard)         |

## Routes

| Method | Path            | Name        | Description        | Auth  | Role  |
|--------|-----------------|-------------|--------------------|-------|-------|
| GET    | /user           | user.index  | List all users     | Yes   | Mabes |
| GET    | /user/create    | user.create | Show create form   | Yes   | Mabes |
| POST   | /user           | user.store  | Create user + unit | Yes   | Mabes |
| GET    | /user/{id}/edit | user.edit   | Show edit form     | Yes   | Mabes |
| PATCH  | /user/{id}      | user.update | Update user + unit | Yes   | Mabes |

## Create User Form Fields

| Field                 | Type     | Rules                     | Description               |
|-----------------------|----------|---------------------------|---------------------------|
| email                 | email    | required, string, unique  | User login email          |
| password              | password | required, confirmed       | User password             |
| password_confirmation | password | required                  | Password confirmation     |
| nama_unit             | string   | required                  | Organizational unit name  |
| kode                  | string   | required                  | Unit code                 |
| level_unit            | enum     | required                  | Unit level (satuan_kerja, satuan_induk, mabes) |
| id_unit_atasan        | integer  | required, FK              | Parent unit (via id_user) |

### Data Created
- `users` record: email, hashed password
- `unit` record: nama_unit, kode, level_unit, id_user (FK to users.id), id_unit_atasan

## Update User Form Fields

| Field     | Type   | Rules    | Description              |
|-----------|--------|----------|--------------------------|
| email     | email  | required | Updated email            |
| kode      | string | required | Updated unit code        |
| nama_unit | string | required | Unit name (pass-through) |

### Data Updated
- `users` record: email
- `unit` record: kode

---

## Test Scenarios

### Happy Path
1. Mabes can access the user list page
2. Mabes can access the create user form
3. Mabes creates a user with email, password, unit name, code, level, and parent unit — records created in both `users` and `unit` tables
4. After creating a user, redirected to user list
5. Mabes can access the edit user form
6. Mabes updates user email and unit code — both tables updated, old data replaced
7. After updating a user, redirected to user list

### Access Control
1. Non-Mabes user accessing user list → redirected to dashboard
2. Non-Mabes user accessing create user form → redirected to dashboard
3. Non-Mabes user accessing edit user form → redirected to dashboard
