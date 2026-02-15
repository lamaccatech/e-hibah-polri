<?php

namespace App\Enums;

enum ProposalChapter: string
{
    case General = 'UMUM';
    case Purpose = 'MAKSUD';
    case Objective = 'TUJUAN';
    case Target = 'SASARAN_KEGIATAN';
    case Benefit = 'MANFAAT_KEGIATAN';
    case ImplementationPlan = 'RENCANA_PELAKSANAAN_KEGIATAN';
    case BudgetPlan = 'RENCANA_KEBUTUHAN_ANGGARAN_KEGIATAN';
    case ReportingPlan = 'RENCANA_PELAPORAN';
    case EvaluationPlan = 'RENCANA_EVALUASI';
    case Closing = 'PENUTUP';
    case ReceptionBasis = 'DASAR_PENERIMAAN_HIBAH';
    case SupervisionMechanism = 'MEKANISME_PENGAWASAN_HIBAH';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Umum',
            self::Purpose => 'Maksud',
            self::Objective => 'Tujuan',
            self::Target => 'Sasaran Kegiatan',
            self::Benefit => 'Manfaat Kegiatan',
            self::ImplementationPlan => 'Rencana Pelaksanaan Kegiatan',
            self::BudgetPlan => 'Rencana Kebutuhan Anggaran Kegiatan',
            self::ReportingPlan => 'Rencana Pelaporan',
            self::EvaluationPlan => 'Rencana Evaluasi',
            self::Closing => 'Penutup',
            self::ReceptionBasis => 'Dasar Penerimaan Hibah',
            self::SupervisionMechanism => 'Mekanisme Pengawasan Hibah',
        };
    }

    /**
     * @return string[]
     */
    public function prompts(): array
    {
        return match ($this) {
            self::General => [
                'Jelaskan latar belakang kebutuhan',
                'Jelaskan informasi relevan untuk mendukung urgensi kegiatan',
                'Uraikan bagaimana kegiatan dapat memberikan solusi',
            ],
            self::Target => [
                'Jelaskan tentang sasaran atas indikator-indikator yang dapat ditingkatkan untuk pencapaian tujuan',
            ],
            self::Benefit => [
                'Jelaskan manfaat kegiatan yang dapat mendukung tugas Polri dan Masyarakat',
                'Jelaskan dampak, baik langsung maupun tidak langsung, kepada calon pemberi hibah',
            ],
            self::ImplementationPlan => [
                'Jelaskan langkah-langkah yang akan dilaksanakan',
            ],
            self::ReportingPlan => [
                'Jelaskan rencana pelaporan kepada calon pemberi hibah',
            ],
            self::EvaluationPlan => [
                'Jelaskan rencana evaluasi yang akan dilakukan oleh Polri',
            ],
            default => [],
        };
    }
}
