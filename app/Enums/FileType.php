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
            self::Attachment => 'Lampiran',
            self::DraftAgreement => 'Draft Perjanjian',
            self::Agreement => 'Naskah Perjanjian',
            self::DonorLetter => 'Surat Pemberi Hibah',
            self::Signature => 'Tanda Tangan',
            self::GeneratedDocument => 'Dokumen Hasil Cetak',
        };
    }
}
