# e-Hibah Polri — Product Spec

## Overview

e-Hibah is a grant management system for the Indonesian National Police (Polri). It manages the full lifecycle of grant proposals through a multi-level approval workflow across three organizational levels.

## Terminology

| Indonesian         | English              | Description                                      |
|--------------------|----------------------|--------------------------------------------------|
| Hibah              | Grant                | The core entity — a grant from an external donor |
| Perencanaan        | Planning             | First stage — planning the grant proposal        |
| Perjanjian         | Agreement            | Second stage — formalizing the grant agreement   |
| Satuan Kerja       | Work Unit (Satker)   | Creates and submits proposals                    |
| Satuan Induk/Polda | Regional Command     | First-level review of proposals                  |
| Mabes              | Headquarters (HQ)    | Final review and system administration           |
| Pemberi Hibah      | Donor                | External entity providing the grant              |
| Pengkajian         | Assessment           | Structured review of a proposal                  |
| Penomoran          | Numbering            | Official number issued on approval               |
| RAB                | Budget Plan          | Cost breakdown for the grant                     |
| Sehati             | Finance Ministry Sub.| Data submission to Ministry of Finance            |

## Grant Types

| Type     | Indonesian | Description                                        |
|----------|------------|----------------------------------------------------|
| Planned  | Terencana  | Goes through Planning → Agreement                  |
| Direct   | Langsung   | Skips Planning, starts directly at Agreement stage |

## Grant Stages

```
Planned:   ┌──────────┐      ┌──────────┐
           │ Planning │ ───► │Agreement │
           └──────────┘      └──────────┘

Direct:                      ┌──────────┐
                             │Agreement │
                             └──────────┘
```

Both stages follow the same approval workflow described below.

## Grant Form

A grant can take one of three forms:

| Form     | Indonesian | Description         |
|----------|------------|---------------------|
| Money    | Uang       | Financial grant     |
| Goods    | Barang     | Equipment/materials |
| Services | Jasa       | Service-based grant |

---

## Satker Workflow: Filling the Proposal

Before submitting for review, Satker completes a multi-step form. The steps differ slightly between Planning and Agreement.

### Planning Stage Steps

| Step | Description                           |
|------|---------------------------------------|
| 1    | Initialize — enter grant name         |
| 2    | Donor info — select or create donor   |
| 3    | Proposal document — fill chapters     |
| 4    | Assessment document — fill 4 aspects  |
| 5    | Submit to Polda                       |

#### Proposal Document Chapters (Planning)

1. Objectives
2. Benefits
3. Implementation plan
4. Budget
5. Reporting & evaluation
6. Closing

### Agreement Stage Steps

| Step | Description                                          |
|------|------------------------------------------------------|
| 1    | Basic reception data — grant name, donor letter      |
| 2    | Donor info — confirm or update donor details         |
| 3    | Assessment document — fill 4 aspects                 |
| 4    | Harmonization — grant form, budget, withdrawal plan  |
| 5    | Additional materials (direct grants only, optional)  |
| 6    | Upload draft agreement document                      |
| 7    | Submit to Polda                                      |

#### Additional Materials (Direct Grants Only)

When a grant starts directly at Agreement (no Planning stage), Satker may provide:
- Activity objectives
- Benefits
- Implementation schedule
- Reporting plan
- Evaluation plan

---

## Approval Workflow

After Satker submits, the proposal enters a review cycle. This flow is identical for both Planning and Agreement stages.

```
  Satker              Polda               Mabes
┌─────────┐      ┌─────────────┐      ┌─────────────┐
│         │      │             │      │             │
│ Submit ──┼────► │   Assess    │      │             │
│          │      │  4 aspects  │      │             │
│          │      │      │      │      │             │
│          │      │      ▼      │      │             │
│          │ ◄──── Request Rev. │      │             │
│  Revise  │      │      │      │      │             │
│          │      │   Reject    │      │             │
│          │      │      │      │      │             │
│          │      │   Verify ───┼────► │   Assess    │
│          │      │             │      │  4 aspects  │
│          │      │             │      │      │      │
│          │ ◄────┼─────────────┼───── Request Rev. │
│  Revise  │      │             │      │      │      │
│          │      │             │      │   Reject    │
│          │      │             │      │      │      │
│          │      │             │      │   Verify    │
│          │      │             │      │   (Final)   │
│          │      │             │      │      │      │
│          │      │             │      │      ▼      │
│          │      │             │      │  Number     │
│          │      │             │      │  Issued     │
└─────────┘      └─────────────┘      └─────────────┘
```

### Assessment (Pengkajian)

Reviewers (Polda and Mabes) evaluate the proposal on **4 mandatory aspects**:

| Aspect    | Indonesian | What is evaluated                                  |
|-----------|------------|----------------------------------------------------|
| Technical | Teknis     | Fit with Polri standards, internal security needs  |
| Economic  | Ekonomis   | Cost-benefit ratio, budget synergy                 |
| Political | Politis    | Impact on Polri independence, bilateral relations  |
| Strategic | Strategis  | Alignment with Polri vision, capability building   |

Each aspect receives one of three results:

| Result   | Indonesian | Meaning                    |
|----------|------------|----------------------------|
| Fulfilled| Terpenuhi  | Aspect meets requirements  |
| Revision | Revisi     | Aspect needs changes       |
| Rejected | Ditolak    | Aspect fails requirements  |

### Decision Logic

| Condition                         | Outcome          |
|-----------------------------------|------------------|
| All 4 aspects fulfilled           | Verify (approve) |
| Any aspect rejected               | Reject           |
| Any aspect needs revision (none rejected) | Request revision |

### Revision Cycle

When revision is requested (by Polda or Mabes), the proposal returns to Satker. Satker revises and resubmits. The review cycle repeats until all aspects are fulfilled or the proposal is rejected.

---

## Post-Approval

After Mabes verifies, an official number is issued automatically. Then:

| Step | Description                                              | Stage     |
|------|----------------------------------------------------------|-----------|
| 1    | Number issued — auto-generated on Mabes verification     | Both      |
| 2    | Upload signed agreement document                         | Agreement |
| 3    | Submit data to Ministry of Finance (Sehati)              | Agreement |

---

## Proposal Statuses

### Planning Stage

| Status                              | Actor   | Description                         |
|-------------------------------------|---------|-------------------------------------|
| Planning initialized                | Satker  | Grant created, filling basic info   |
| Filling donor info                  | Satker  | Entering donor details              |
| Creating proposal document          | Satker  | Writing proposal chapters           |
| Creating assessment document        | Satker  | Filling 4 assessment aspects        |
| Submitted to Polda                  | Satker  | Waiting for Polda review            |
| Under Polda review                  | Polda   | Polda assessing 4 aspects           |
| Verified by Polda                   | Polda   | Approved, forwarded to Mabes        |
| Rejected by Polda                   | Polda   | Proposal rejected                   |
| Revision requested by Polda         | Polda   | Returned to Satker for changes      |
| Revising (Polda feedback)           | Satker  | Satker making requested changes     |
| Revision resubmitted to Polda       | Satker  | Revised proposal sent back          |
| Under Mabes review                  | Mabes   | Mabes assessing 4 aspects           |
| Verified by Mabes                   | Mabes   | Proposal approved                   |
| Rejected by Mabes                   | Mabes   | Proposal rejected                   |
| Revision requested by Mabes         | Mabes   | Returned to Satker for changes      |
| Revising (Mabes feedback)           | Satker  | Satker making requested changes     |
| Revision resubmitted to Mabes       | Satker  | Revised proposal sent back          |
| Planning number issued              | System  | Official number auto-generated      |

### Agreement Stage

| Status                              | Actor   | Description                         |
|-------------------------------------|---------|-------------------------------------|
| Filling basic reception data        | Satker  | Entering grant basics               |
| Filling donor info                  | Satker  | Entering/confirming donor details   |
| Creating assessment document        | Satker  | Filling 4 assessment aspects        |
| Filling harmonization data          | Satker  | Budget, withdrawal plan, grant form |
| Filling additional materials        | Satker  | Extra info (direct grants only)     |
| Filling other materials             | Satker  | Additional readiness info           |
| Uploading draft agreement           | Satker  | Uploading draft document            |
| Submitted to Polda                  | Satker  | Waiting for Polda review            |
| Under Polda review                  | Polda   | Polda assessing 4 aspects           |
| Verified by Polda                   | Polda   | Approved, forwarded to Mabes        |
| Rejected by Polda                   | Polda   | Agreement rejected                  |
| Revision requested by Polda         | Polda   | Returned to Satker for changes      |
| Revising (Polda feedback)           | Satker  | Satker making requested changes     |
| Revision resubmitted to Polda       | Satker  | Revised agreement sent back         |
| Under Mabes review                  | Mabes   | Mabes assessing 4 aspects           |
| Verified by Mabes                   | Mabes   | Agreement approved                  |
| Rejected by Mabes                   | Mabes   | Agreement rejected                  |
| Revision requested by Mabes         | Mabes   | Returned to Satker for changes      |
| Revising (Mabes feedback)           | Satker  | Satker making requested changes     |
| Revision resubmitted to Mabes       | Satker  | Revised agreement sent back         |
| Agreement number issued             | System  | Official number auto-generated      |
| Uploading signed agreement          | Satker  | Uploading final signed document     |
| Submitting to Finance Ministry      | Satker  | Entering Sehati data for Kemenkeu   |

---

## Data Entities

| Entity                       | Table (Indonesian)                  | Description                          |
|------------------------------|-------------------------------------|--------------------------------------|
| Grant                        | hibah                               | Core grant record                    |
| Donor                        | pemberi_hibah                       | External grant provider              |
| OrgUnit                      | unit                                | Organizational unit (Satker/Polda/HQ)|
| OrgUnitChief                 | kepala_unit                         | Unit leadership info                 |
| GrantStatusHistory           | riwayat_perubahan_status_hibah      | Audit trail of status changes        |
| GrantAssessment              | pengkajian_hibah                    | Assessment record (links to status)  |
| GrantAssessmentContent       | isi_pengkajian_hibah                | Assessment paragraph content         |
| GrantAssessmentResult        | hasil_pengkajian_hibah              | Per-aspect result by reviewer        |
| GrantInformation             | informasi_hibah                     | Proposal/agreement info sections     |
| GrantInformationContent      | isi_informasi_hibah                 | Section content                      |
| GrantDocument                | dokumen_hibah                       | Document attachments                 |
| GrantNumbering               | penomoran_hibah                     | Official numbering record            |
| GrantWithdrawalPlan          | rencana_penarikan_hibah             | Withdrawal schedule                  |
| GrantLocationAndAllocation   | lokasi_dan_alokasi_hibah            | Location and fund allocation         |
| GrantBudgetPlan              | rencana_anggaran_biaya_hibah        | Budget breakdown (RAB)               |
| ActivitySchedule             | jadwal_pelaksanaan_kegiatan         | Activity timeline                    |
| GrantFinanceMinistrySubmission| informasi_hibah_untuk_sehati       | Data for Ministry of Finance         |

---

## Key Rules

1. **No self-registration** — All accounts are provisioned by Mabes
2. **Planning is optional** — Direct grants skip to Agreement
3. **Same review workflow** — Both stages use the identical 4-aspect assessment
4. **Sequential approval** — Polda must verify before Mabes can review
5. **Revisions return to Satker** — Both Polda and Mabes can request changes
6. **Assessment-driven decisions** — Verify/reject/revise is determined by the 4 aspect results
7. **Auto-numbering** — Official number issued automatically on Mabes verification
8. **Audit trail** — Every status change is logged with timestamp and actor
