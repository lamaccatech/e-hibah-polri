<?php

namespace App\Enums;

enum FileType: string
{
    case Attachment = 'LAMPIRAN';
    case DraftAgreement = 'DRAFT_PERJANJIAN';
    case Agreement = 'NASKAH_PERJANJIAN';
    case DonorLetter = 'SURAT_PEMBERI_HIBAH';
    case Signature = 'TANDA_TANGAN';
    case GeneratedDocument = 'DOKUMEN_HASIL_CETAK';

    public function label(): string
    {
        return match ($this) {
            self::Attachment => __('common.file-type.attachment'),
            self::DraftAgreement => __('common.file-type.draft-agreement'),
            self::Agreement => __('common.file-type.agreement'),
            self::DonorLetter => __('common.file-type.donor-letter'),
            self::Signature => __('common.file-type.signature'),
            self::GeneratedDocument => __('common.file-type.generated-document'),
        };
    }
}
