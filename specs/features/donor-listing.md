# Feature: Donor Listing

## Overview

Mabes can browse a read-only listing of all donors across the system. Donors are created by Satker during grant flows — Mabes does not create or edit donors, only views and categorizes them (via tags).

## Business Rules

- Only Mabes can access the donor listing page
- Listing is read-only — no create/edit/delete
- Searchable by donor name (`nama`)
- Paginated (15 per page)
- Each row shows: nama, asal, kategori, negara, number of linked grants
- Clicking a donor shows detail with full info + linked grants + assigned tags
- Tag assignment on donors is handled by the grant categorization feature

## Actors

| Actor  | Permissions                      |
|--------|----------------------------------|
| Mabes  | View donor list, view detail     |
| Others | No access (redirected)           |

## Routes

| Method | Path | Name | Description |
|--------|------|------|-------------|
| GET | /donor | donor.index | List all donors |
| GET | /donor/{donor} | donor.show | View donor detail |

## Donor List Page

**Component:** `DonorListing\Index`

### Table Columns

| Column | Source |
|--------|--------|
| Nama | `donor.nama` |
| Asal | `donor.asal` |
| Kategori | `donor.kategori` |
| Negara | `donor.negara` |
| Jumlah Hibah | `donor.grants_count` |

### Search
- `wire:model.live.debounce.300ms` on `nama` field
- Uses `ilike` for case-insensitive search

## Donor Detail Page

**Component:** `DonorListing\Show`

### Sections

1. **Informasi Pemberi Hibah** — All donor fields (nama, asal, alamat, negara, regional info, phone, email, kategori)
2. **Daftar Hibah** — Table of grants linked to this donor (nama_hibah, satker name, status, link to grant detail)
3. **Kategori** — List of assigned tags (read-only display; managed via grant categorization feature)

## Test Scenarios

### Happy Path
1. Mabes can access donor list page
2. Mabes sees all donors with grant counts
3. Mabes can search donors by name
4. Mabes can view donor detail with linked grants

### Access Control
5. Non-Mabes user redirected to dashboard from donor list
6. Non-Mabes user redirected to dashboard from donor detail
