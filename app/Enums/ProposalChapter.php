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
            self::General => __('common.proposal-chapter.general'),
            self::Purpose => __('common.proposal-chapter.purpose'),
            self::Objective => __('common.proposal-chapter.objective'),
            self::Target => __('common.proposal-chapter.target'),
            self::Benefit => __('common.proposal-chapter.benefit'),
            self::ImplementationPlan => __('common.proposal-chapter.implementation-plan'),
            self::BudgetPlan => __('common.proposal-chapter.budget-plan'),
            self::ReportingPlan => __('common.proposal-chapter.reporting-plan'),
            self::EvaluationPlan => __('common.proposal-chapter.evaluation-plan'),
            self::Closing => __('common.proposal-chapter.closing'),
            self::ReceptionBasis => __('common.proposal-chapter.reception-basis'),
            self::SupervisionMechanism => __('common.proposal-chapter.supervision-mechanism'),
        };
    }

    /**
     * @return string[]
     */
    public function prompts(): array
    {
        return match ($this) {
            self::General => [
                __('common.proposal-chapter.prompt-general-1'),
                __('common.proposal-chapter.prompt-general-2'),
                __('common.proposal-chapter.prompt-general-3'),
            ],
            self::Target => [
                __('common.proposal-chapter.prompt-target-1'),
            ],
            self::Benefit => [
                __('common.proposal-chapter.prompt-benefit-1'),
                __('common.proposal-chapter.prompt-benefit-2'),
            ],
            self::ImplementationPlan => [
                __('common.proposal-chapter.prompt-implementation-1'),
            ],
            self::ReportingPlan => [
                __('common.proposal-chapter.prompt-reporting-1'),
            ],
            self::EvaluationPlan => [
                __('common.proposal-chapter.prompt-evaluation-1'),
            ],
            default => [],
        };
    }
}
