# Agreement Flow (Perjanjian Hibah)

## Overview

The agreement flow is the second major stage of the grant lifecycle. After a planning proposal (usulan) is approved and a planning number is issued, or when a grant comes in directly without a proposal, the Satker fills in the agreement data through 7 steps.

## Entry Points

| Source | Condition | Behavior |
|--------|-----------|----------|
| From planning | `ada_usulan = true`, planning number issued | **Same grant** transitions from `tahapan = Planning` to `tahapan = Agreement`. Status before = `PlanningNumberIssued`. Planning data (Maksud, Tujuan, Sasaran, Manfaat, Rencana Pelaksanaan, Rencana Pelaporan, Rencana Evaluasi, Budget, Donor) is copied as agreement-stage records. |
| Direct (no proposal) | `ada_usulan = false` | **New grant** created with `tahapan = Agreement`, `jenis_hibah = Direct`. No data to copy. |

When coming from planning, we refer to this as "linked to planning" or "has proposal" (`ada_usulan = true`).

## Linking via Nomor Surat

In Step 1, the Satker enters a "Nomor Surat". The system checks if this value matches a `penomoran_hibah.nomor` (where `tahapan = Planning`) for a grant belonging to the same Satker. If matched:
- The **existing grant** is used (not a new one)
- `grant.tahapan` is updated to `Agreement`
- `grant.ada_usulan` remains `true`
- Planning data is copied to agreement-stage records (new `GrantInformation` rows with `tahapan = Agreement`)
- `status_sebelum` on the new status history = `PlanningNumberIssued`

If not matched:
- A **new grant** is created with `ada_usulan = false`
- `status_sebelum` is null (first status entry)
- Satker must upload "Surat dari Pemberi Hibah"

## Data Copy from Planning (ada_usulan = true)

When saving Step 1 with a linked planning grant, the following planning data is duplicated as agreement-stage records:

| Planning Source | Copy Target | Timing |
|-----------------|-------------|--------|
| Maksud (Purpose chapter) | `GrantInformation` with `tahapan = Agreement` | Step 1 save |
| Tujuan (Objective chapter) | Submitted by user in Step 1 (may differ from planning) | Step 1 save |
| Sasaran Kegiatan | `GrantInformation` with `tahapan = Agreement` | Step 1 save |
| Manfaat Kegiatan | `GrantInformation` with `tahapan = Agreement` | Step 1 save |
| Rencana Pelaksanaan | `GrantInformation` with `tahapan = Agreement` | Step 1 save |
| Rencana Pelaporan | `GrantInformation` with `tahapan = Agreement` | Step 1 save |
| Rencana Evaluasi | `GrantInformation` with `tahapan = Agreement` | Step 1 save |
| Donor | Already linked on grant | Step 2 read-only |
| Assessment (4 aspects + custom) | Pre-filled in Step 3 form (editable) | Step 3 load |
| Budget items | Pre-filled in Step 4 form (editable) | Step 4 load |

For planned grants, Step 5 (Additional Materials) is **skipped entirely** — the Sasaran, Manfaat, and Rencana chapters are already copied as agreement-stage records at Step 1 save.

"Pre-filled" means the planning data is loaded into the form as initial values. The Satker can edit before saving. On save, new `GrantInformation`/`GrantAssessment` records are created with `tahapan = Agreement`.

## Routes

```
Middleware: auth, verified, satker

GET /grant-agreement                            → Index
GET /grant-agreement/create                     → ReceptionBasis (new, ada_usulan=false)
GET /grant-agreement/{grant}/reception-basis     → ReceptionBasis (edit)
GET /grant-agreement/{grant}/donor               → DonorInfo
GET /grant-agreement/{grant}/assessment          → Assessment
GET /grant-agreement/{grant}/harmonization       → Harmonization
GET /grant-agreement/{grant}/additional          → AdditionalMaterials
GET /grant-agreement/{grant}/other               → OtherMaterials
GET /grant-agreement/{grant}/draft               → DraftAgreement
```

Route names: `grant-agreement.{index,create,reception-basis,donor,assessment,harmonization,additional,other,draft}`

## Step Layout

Uses `<x-grant-agreement.step-layout>` with 7 steps in the sidebar navigation (same pattern as planning's 4-step layout):

| Step | Label | Status |
|------|-------|--------|
| 1 | Dasar Penerimaan | `FillingReceptionData` |
| 2 | Pemberi Hibah | `FillingDonorInfo` |
| 3 | Kajian | `CreatingAgreementAssessment` |
| 4 | Harmonisasi Naskah | `FillingHarmonization` |
| 5 | Materi Tambahan Kesiapan | `FillingAdditionalMaterials` |
| 6 | Materi Tambahan Lainnya | `FillingOtherMaterials` |
| 7 | Draft Naskah Perjanjian | `UploadingDraftAgreement` |

---

## Step 1: Dasar Penerimaan

**Component:** `GrantAgreement\ReceptionBasis`
**Status:** `FillingReceptionData`

### Fields

#### 1.1 Nama Kegiatan (`activityName`)
- Text input, uppercase forced
- Maps to `grant.nama_hibah`
- Required, string, max:255
- Pre-filled from grant when editing or when linked to planning

#### 1.2 Nomor Surat (`letterNumber`)
- Text input
- Maps to `grant.nomor_surat_dari_calon_pemberi_hibah`
- Required, string, max:255
- On input change (debounced), check if value matches a planning number for the same Satker
- When matched: show indicator that this is linked to planning, pre-fill activity name

#### 1.3 Surat dari Pemberi Hibah (`donorLetter`) — conditional
- File upload, shown **only when** `ada_usulan = false` (no matched planning number)
- Required when shown
- Attached to `GrantStatusHistory` via `HasFiles`, type `FileType::DonorLetter`
- Accepted: PDF, JPG, PNG, max 10MB

#### 1.4 Tujuan (`objectives`)
- Multiple entries (min 1), add/remove
- Each entry:
  - `purpose`: combobox select from `config('options.grant_purposes')`
  - `detail`: rich text editor, min 10 chars
- When linked to planning: pre-filled from planning's Tujuan chapter (editable)
- Stored as `GrantInformation` with `judul = ProposalChapter::Objective`, `tahapan = Agreement`
- Each entry → `GrantInformationContent` with `subjudul = purpose`, `isi = detail`

### Save Behavior

**New agreement (no planning):**
- Creates new grant: `tahapan = Agreement`, `jenis_hibah = Direct`, `ada_usulan = false`
- Creates status history: `status_sesudah = FillingReceptionData`, `status_sebelum = null`
- Saves objectives as `GrantInformation` with `tahapan = Agreement`
- Attaches donor letter file to status history

**From planning:**
- Updates existing grant: `tahapan = Agreement` (keep existing `jenis_hibah`, `ada_usulan = true`)
- Creates status history: `status_sesudah = FillingReceptionData`, `status_sebelum = PlanningNumberIssued`
- Copies Maksud chapter from planning to agreement-stage `GrantInformation`
- Saves objectives (submitted by user) as agreement-stage `GrantInformation`
- Copies Sasaran, Manfaat, Rencana Pelaksanaan, Rencana Pelaporan, Rencana Evaluasi from planning

**Edit (existing agreement):**
- Updates grant fields
- Upserts objectives

Redirect to Step 2.

---

## Step 2: Pemberi Hibah

**Component:** `GrantAgreement\DonorInfo`
**Status:** `FillingDonorInfo`

### Behavior
- Same form as planning DonorInfo (Step 2): donor name search, origin, address, country, category, phone, email, regional selectors.
- **If `ada_usulan = true`:** Form is **read-only**. Donor data already linked from planning. Display info but disable all inputs. Just a "Lanjut" button to navigate to Step 3.
- **If `ada_usulan = false`:** Full editable donor form, same as planning.

### Save Behavior
- When editable: creates/links donor via repository, creates status `FillingDonorInfo`.
- When read-only: no save, just navigate to Step 3 (status `FillingDonorInfo` created for tracking).
- **If `ada_usulan = false`:** After saving donor, auto-generate a Purpose section (`GrantInformation` with `judul = ProposalChapter::Purpose`, `tahapan = Agreement`). This provides the Maksud that planned grants already have from copy.

---

## Step 3: Kajian

**Component:** `GrantAgreement\Assessment`
**Status:** `CreatingAgreementAssessment`

### Behavior
- Same form as planning Assessment (Step 4): 4 mandatory aspects (Technical, Economic, Political, Strategic) + optional custom aspects.
- **If `ada_usulan = true`:** Values from planning assessment are **pre-filled** (copied from `CreatingPlanningAssessment` status history's assessments). Satker can edit.
- **If `ada_usulan = false`:** Empty form.
- Stored as `GrantAssessment` + `GrantAssessmentContent` linked to status history, with `tahapan = Agreement`.

---

## Step 4: Harmonisasi Naskah

**Component:** `GrantAgreement\Harmonization`
**Status:** `FillingHarmonization`

### Fields

#### 4.1 Bentuk Hibah (`grantForms`)
- Checkbox group (multiple select): Uang, Barang, Jasa
- Options from `config('options.grant_forms')`: `['UANG', 'BARANG', 'JASA']`
- Maps to `grant.bentuk_hibah`
- Stored as CSV string using `StringCollectionCast`
- Required (at least one selected)

#### 4.2 Rencana Anggaran Kebutuhan
- **Mata Uang** (`currency`): combobox from `Autocomplete` where `identifier = mata_uang`
- Maps to `grant.mata_uang`
- **Budget Items** (`budgetItems`): array of `{ uraian, nilai }`
  - Same pattern as planning ProposalDocument budget items
  - Stored in `rencana_anggaran_biaya_hibah` (GrantBudgetPlan)
  - **If `ada_usulan = true`:** Pre-filled from planning budget items. Editable.

#### 4.3 Rencana Penarikan Hibah (`withdrawalPlans`)
- Array of `{ uraian, tanggal, nilai }`
- Stored in `rencana_penarikan_hibah` (GrantWithdrawalPlan)
- **Validation:** Total `nilai` of all withdrawal plans must not exceed total `nilai` of budget items
- Add/remove entries, min 1

#### 4.4 Mekanisme Pengawasan Hibah (`supervisionParagraphs`)
- Array of rich text editors (multiple paragraphs)
- "Tambah Paragraf" button to add more editors
- Stored as `GrantInformation` with `judul = ProposalChapter::SupervisionMechanism`, `tahapan = Agreement`
- Each paragraph → `GrantInformationContent` with `isi = content`, `nomor_urut = index`

#### 4.5 Auto-calculated Grant Value
- `grant.nilai_hibah = SUM(budgetItems[].nilai)`
- Calculated on save, not user-editable

### Save Behavior
- Update grant: `bentuk_hibah`, `mata_uang`, `nilai_hibah` (auto-calculated)
- Upsert budget items, withdrawal plans, supervision paragraphs
- Status: `FillingHarmonization`
- **If `ada_usulan = false`:** Redirect to Step 5 (Additional Materials)
- **If `ada_usulan = true`:** Redirect to Step 6 (Other Materials) — Step 5 is skipped

---

## Step 5: Materi Tambahan Kesiapan

**Component:** `GrantAgreement\AdditionalMaterials`
**Status:** `FillingAdditionalMaterials`

**Skipped when `ada_usulan = true`** — planned grants already have these sections copied from planning at Step 1. The step layout still shows Step 5 but it is skipped in the navigation flow (Step 4 redirects directly to Step 6).

### Fields

Only shown for direct grants (`ada_usulan = false`). All fields use the same chapter-based editor pattern as planning ProposalDocument. Each is stored as `GrantInformation` + `GrantInformationContent` with `tahapan = Agreement`.

| # | Field | ProposalChapter | Prompts |
|---|-------|-----------------|---------|
| 5.1 | Sasaran Kegiatan | `Target` | Same prompts as planning |
| 5.2 | Manfaat Kegiatan | `Benefit` | Same prompts as planning |
| 5.3 | Rencana Pelaksanaan Kegiatan | `ImplementationPlan` | Same prompts as planning |
| 5.4 | Rencana Pelaporan | `ReportingPlan` | Same prompts as planning |
| 5.5 | Rencana Evaluasi | `EvaluationPlan` | Same prompts as planning |

### Save Behavior
- Upsert all chapter data with `tahapan = Agreement`
- Status: `FillingAdditionalMaterials`
- Redirect to Step 6

---

## Step 6: Materi Tambahan Lainnya

**Component:** `GrantAgreement\OtherMaterials`
**Status:** `FillingOtherMaterials`

### Behavior
- **Entirely optional** — can be skipped
- Same custom chapter pattern as planning ProposalDocument custom chapters
- Array of `{ title, paragraphs[] }` — add/remove chapters, add/remove paragraphs per chapter
- Stored as `GrantInformation` (custom `judul`) + `GrantInformationContent` with `tahapan = Agreement`
- "Lewati" (Skip) button alongside "Simpan & Lanjut"

### Save Behavior
- If skipped: status `FillingOtherMaterials` created, navigate to Step 7
- If saved: upsert custom chapters, status `FillingOtherMaterials`
- Redirect to Step 7

---

## Step 7: Draft Naskah Perjanjian

**Component:** `GrantAgreement\DraftAgreement`
**Status:** `UploadingDraftAgreement`

### Fields

#### 7.1 File Upload (`draftFile`)
- File upload for draft agreement document
- Attached to `GrantStatusHistory` via `HasFiles`, type `FileType::DraftAgreement`
- Required
- Accepted: PDF, max 20MB

### Save Behavior
- Upload file, attach to status history
- Status: `UploadingDraftAgreement`
- Redirect to agreement index

---

## Repository: `GrantAgreementRepository`

New repository handling all agreement CRUD operations.

### Key Methods
- `createAgreement(OrgUnit, activityName, letterNumber): Grant` — new grant, ada_usulan=false
- `transitionFromPlanning(Grant, letterNumber, objectives[]): void` — existing grant transitions to agreement, copies planning data
- `updateReceptionBasis(Grant, activityName, letterNumber): void`
- `saveObjectives(Grant, objectives[]): void`
- `copyPlanningDataToAgreement(Grant): void` — copies Maksud, Sasaran, Manfaat, Rencana Pelaksanaan/Pelaporan/Evaluasi
- `linkDonor(Grant, donorId): void`
- `saveAssessment(Grant, aspects[]): void`
- `saveHarmonization(Grant, grantForms, currency, budgetItems, withdrawalPlans, supervisionParagraphs): void`
- `saveAdditionalMaterials(Grant, chapters): void`
- `saveOtherMaterials(Grant, customChapters): void`
- `saveDraftAgreement(Grant): void`
- `findPlanningGrantByNumber(string letterNumber, OrgUnit unit): ?Grant` — lookup by planning number
- `isEditable(Grant): bool`
- `getLatestStatus(Grant): ?GrantStatus`

---

## Schema Changes

### Grant model cast change
- `bentuk_hibah`: change from `GrantForm::class` to `StringCollectionCast::class` (supports CSV multi-value)

### New config key
- `config('options.grant_forms')`: `['UANG', 'BARANG', 'JASA']`

### No new tables
All data fits into existing tables:
- `hibah` — grant fields
- `rencana_anggaran_biaya_hibah` — budget items
- `rencana_penarikan_hibah` — withdrawal plans
- `informasi_hibah` + `isi_informasi_hibah` — chapters (objectives, supervision, additional materials, other materials)
- `pengkajian_hibah` + `isi_pengkajian_hibah` — assessments
- `riwayat_perubahan_status_hibah` — status history
- `files` — uploaded files (donor letter, draft agreement)
