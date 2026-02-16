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
}
