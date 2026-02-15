# Feature: Unit Chief Management

## Overview

Satker (work units) manage their unit chiefs (kepala unit). A unit chief represents the leadership of a Satker and includes personal details plus a signature image used for official documents. Exactly one chief can be designated as the active (currently serving) chief at any time.

## Business Rules

- Only Satker users can access unit chief management pages
- Non-Satker users are redirected to the dashboard
- Each chief belongs to a specific organizational unit (via `id_unit` → `unit.id_user`)
- Exactly one chief can be designated as `sedang_menjabat = true` (currently serving)
- A signature image (PNG) is required when creating a chief
- Signature image can be updated when editing a chief

## Actors

| Actor  | Permissions                                      |
|--------|--------------------------------------------------|
| Satker | Create, list, update, designate active chief     |
| Others | No access (redirected to dashboard)              |

## Routes

| Method | Path                      | Name                 | Description              | Auth  | Role   |
|--------|---------------------------|----------------------|--------------------------|-------|--------|
| GET    | /kepala-satker            | kepala-satker.index  | List unit chiefs         | Yes   | Satker |
| GET    | /kepala-satker/create     | kepala-satker.create | Show create form         | Yes   | Satker |
| POST   | /kepala-satker            | kepala-satker.store  | Create new chief         | Yes   | Satker |
| GET    | /kepala-satker/{id}/edit  | kepala-satker.edit   | Show edit form           | Yes   | Satker |
| PATCH  | /kepala-satker/{id}       | kepala-satker.update | Update chief data        | Yes   | Satker |
| POST   | /kepala-satker/{id}/assign| kepala-satker.assign | Designate active chief   | Yes   | Satker |

## Create Chief Form Fields

| Field           | Type    | Rules            | Description                     |
|-----------------|---------|------------------|---------------------------------|
| id_unit         | integer | required, FK     | Unit reference (unit.id_user)   |
| nama_lengkap    | string  | required         | Full name                       |
| jabatan         | string  | required         | Position/title                  |
| pangkat         | string  | required         | Rank                            |
| nrp             | string  | required         | Personnel ID number             |
| tanda_tangan    | file    | required, image  | Signature image (PNG)           |
| sedang_menjabat | boolean | required         | Currently serving (usually false on create) |

## Update Chief Form Fields

Same fields as create. Signature image is re-uploaded on update.

## Designate Active Chief

POST to the assign route sets `sedang_menjabat = true` for the specified chief.

---

## Test Scenarios

### Happy Path
1. Satker can access the unit chief list page — sees list of chiefs with `data` prop
2. Satker creates a new chief (name, position, rank, NRP, signature image) — record created in `kepala_unit` table
3. After creating, redirected to chief list
4. Satker designates a chief as active — `sedang_menjabat` set to true
5. Satker updates chief data (name, position, rank, NRP, new signature) — record updated, old data replaced
6. After updating, redirected to chief list

### Access Control
1. Non-Satker user accessing chief list → redirected to dashboard
2. Non-Satker user accessing create chief form → redirected to dashboard
3. Non-Satker user accessing edit chief form → redirected to dashboard
