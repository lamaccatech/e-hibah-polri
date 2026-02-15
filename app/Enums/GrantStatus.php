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
}
