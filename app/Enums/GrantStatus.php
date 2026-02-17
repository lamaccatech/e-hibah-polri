<?php

namespace App\Enums;

enum GrantStatus: string
{
    // Planning stage — Satker filling
    case PlanningInitialized = 'INISIALISASI_USULAN_HIBAH';
    case FillingDonorCandidate = 'SATUAN_KERJA_MENGISI_DATA_CALON_PEMBERI_HIBAH';
    case CreatingProposalDocument = 'SATUAN_KERJA_MEMBUAT_NASKAH_USULAN_HIBAH';
    case CreatingPlanningAssessment = 'SATUAN_KERJA_MEMBUAT_DOKUMEN_KAJIAN_USULAN_HIBAH';
    case PlanningSubmittedToPolda = 'SATUAN_KERJA_MENGAJUKAN_USULAN_HIBAH';
    case RevisingPlanning = 'SATUAN_KERJA_MEREVISI_USULAN_HIBAH';
    case PlanningRevisionResubmitted = 'SATUAN_KERJA_MENGAJUKAN_REVISI_USULAN_HIBAH';

    // Planning stage — Polda review
    case PoldaReviewingPlanning = 'SATUAN_INDUK_MENGKAJI_USULAN_HIBAH';
    case PoldaVerifiedPlanning = 'SATUAN_INDUK_MENYETUJUI_USULAN_HIBAH';
    case PoldaRejectedPlanning = 'SATUAN_INDUK_MENOLAK_USULAN_HIBAH';
    case PoldaRequestedPlanningRevision = 'SATUAN_INDUK_MEMINTA_REVISI_USULAN_HIBAH';

    // Planning stage — Mabes review
    case MabesReviewingPlanning = 'MABES_MENGKAJI_USULAN_HIBAH';
    case MabesVerifiedPlanning = 'MABES_MENYETUJUI_USULAN_HIBAH';
    case MabesRejectedPlanning = 'MABES_MENOLAK_USULAN_HIBAH';
    case MabesRequestedPlanningRevision = 'MABES_MEMINTA_REVISI_USULAN_HIBAH';
    case PlanningNumberIssued = 'NOMOR_USULAN_HIBAH_TERBIT';

    // Agreement stage — Satker filling
    case FillingReceptionData = 'SATUAN_KERJA_MENGISI_DATA_DASAR_PENERIMAAN_HIBAH';
    case FillingDonorInfo = 'SATUAN_KERJA_MENGISI_DATA_PEMBERI_HIBAH';
    case CreatingAgreementAssessment = 'SATUAN_KERJA_MEMBUAT_DOKUMEN_KAJIAN_HIBAH';
    case FillingHarmonization = 'SATUAN_KERJA_MENGISI_DATA_HARMONISASI_NASKAH_PERJANJIAN_HIBAH';
    case FillingAdditionalMaterials = 'SATUAN_KERJA_MENGISI_MATERI_TAMBAHAN_KESIAPAN_HIBAH';
    case FillingOtherMaterials = 'SATUAN_KERJA_MENGISI_MATERI_KESIAPAN_HIBAH_LAINNYA';
    case UploadingDraftAgreement = 'SATUAN_KERJA_MENGUPLOAD_DRAFT_NASKAH_PERJANJIAN_HIBAH';
    case AgreementSubmittedToPolda = 'SATUAN_KERJA_MENGAJUKAN_PERJANJIAN_HIBAH';
    case RevisingAgreement = 'SATUAN_KERJA_MEREVISI_PERJANJIAN_HIBAH';
    case AgreementRevisionResubmitted = 'SATUAN_KERJA_MENGAJUKAN_REVISI_PERJANJIAN_HIBAH';

    // Agreement stage — Polda review
    case PoldaReviewingAgreement = 'SATUAN_INDUK_MENGKAJI_PERJANJIAN_HIBAH';
    case PoldaVerifiedAgreement = 'SATUAN_INDUK_MENYETUJUI_PERJANJIAN_HIBAH';
    case PoldaRejectedAgreement = 'SATUAN_INDUK_MENOLAK_PERJANJIAN_HIBAH';
    case PoldaRequestedAgreementRevision = 'SATUAN_INDUK_MEMINTA_REVISI_PERJANJIAN_HIBAH';

    // Agreement stage — Mabes review
    case MabesReviewingAgreement = 'MABES_MENGKAJI_PERJANJIAN_HIBAH';
    case MabesVerifiedAgreement = 'MABES_MENYETUJUI_PERJANJIAN_HIBAH';
    case MabesRejectedAgreement = 'MABES_MENOLAK_PERJANJIAN_HIBAH';
    case MabesRequestedAgreementRevision = 'MABES_MEMINTA_REVISI_PERJANJIAN_HIBAH';
    case AgreementNumberIssued = 'NOMOR_NASKAH_PERJANJIAN_TERBIT';

    // Post-approval
    case UploadingSignedAgreement = 'SATUAN_KERJA_MENGUPLOAD_NASKAH_PERJANJIAN_HIBAH';
    case SubmittingToFinanceMinistry = 'SATUAN_KERJA_MENGISI_DATA_SEHATI_KEMENKEU';

    public function label(): string
    {
        return match ($this) {
            self::PlanningInitialized => __('page.grant-planning.badge-initialized'),
            self::FillingDonorCandidate => __('page.grant-planning.badge-filling-donor'),
            self::CreatingProposalDocument => __('page.grant-planning.badge-creating-proposal'),
            self::CreatingPlanningAssessment => __('page.grant-planning.badge-creating-assessment'),
            self::PlanningSubmittedToPolda => __('page.grant-planning.badge-submitted'),
            self::RevisingPlanning => __('page.grant-planning.badge-revising'),
            self::PlanningRevisionResubmitted => __('page.grant-planning.badge-revision-resubmitted'),
            self::PoldaReviewingPlanning => __('page.grant-review.badge-reviewing'),
            self::PoldaVerifiedPlanning => __('page.grant-review.badge-verified'),
            self::PoldaRejectedPlanning => __('page.grant-review.badge-rejected'),
            self::PoldaRequestedPlanningRevision => __('page.grant-review.badge-revision-requested'),
            self::MabesReviewingPlanning => __('page.mabes-grant-review.badge-reviewing'),
            self::MabesVerifiedPlanning => __('page.mabes-grant-review.badge-verified'),
            self::MabesRejectedPlanning => __('page.mabes-grant-review.badge-rejected'),
            self::MabesRequestedPlanningRevision => __('page.mabes-grant-review.badge-revision-requested'),
            self::PlanningNumberIssued => __('page.mabes-grant-review.badge-number-issued'),
            self::FillingReceptionData => __('page.grant-agreement.badge-filling-reception'),
            self::FillingDonorInfo => __('page.grant-agreement.badge-filling-donor'),
            self::CreatingAgreementAssessment => __('page.grant-agreement.badge-creating-assessment'),
            self::FillingHarmonization => __('page.grant-agreement.badge-filling-harmonization'),
            self::FillingAdditionalMaterials => __('page.grant-agreement.badge-filling-additional'),
            self::FillingOtherMaterials => __('page.grant-agreement.badge-filling-other'),
            self::UploadingDraftAgreement => __('page.grant-agreement.badge-uploading-draft'),
            default => $this->value,
        };
    }

    public function isRejected(): bool
    {
        return in_array($this, [
            self::PoldaRejectedPlanning,
            self::MabesRejectedPlanning,
        ]);
    }

    public function isRevisionRequested(): bool
    {
        return in_array($this, [
            self::PoldaRequestedPlanningRevision,
            self::MabesRequestedPlanningRevision,
        ]);
    }

    public function isEditableBySatker(): bool
    {
        return in_array($this, [
            self::PlanningInitialized,
            self::FillingDonorCandidate,
            self::CreatingProposalDocument,
            self::CreatingPlanningAssessment,
            self::RevisingPlanning,
            self::PoldaRequestedPlanningRevision,
            self::MabesRequestedPlanningRevision,
        ]);
    }

    public function canSubmitForPlanning(): bool
    {
        return $this->isEditableBySatker();
    }

    public function canStartPoldaReview(): bool
    {
        return in_array($this, [
            self::PlanningSubmittedToPolda,
            self::PlanningRevisionResubmitted,
        ]);
    }

    public function isEditableBySatkerAgreement(): bool
    {
        return in_array($this, [
            self::FillingReceptionData,
            self::FillingDonorInfo,
            self::CreatingAgreementAssessment,
            self::FillingHarmonization,
            self::FillingAdditionalMaterials,
            self::FillingOtherMaterials,
            self::UploadingDraftAgreement,
            self::RevisingAgreement,
            self::PoldaRequestedAgreementRevision,
            self::MabesRequestedAgreementRevision,
        ]);
    }

    public function canStartMabesReview(): bool
    {
        return in_array($this, [
            self::PoldaVerifiedPlanning,
        ]);
    }
}
