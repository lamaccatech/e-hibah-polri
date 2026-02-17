<?php

namespace App\Repositories;

use App\Enums\GrantStage;
use App\Models\Grant;
use App\Models\GrantNumbering;
use Illuminate\Support\Facades\DB;

class GrantNumberingRepository
{
    private const PREFIX_PLANNING = 'SUHL';

    private const PREFIX_AGREEMENT = 'NPH';

    public function issuePlanningNumber(Grant $grant): GrantNumbering
    {
        return $this->issueNumber($grant, self::PREFIX_PLANNING);
    }

    public function issueAgreementNumber(Grant $grant): GrantNumbering
    {
        return $this->issueNumber($grant, self::PREFIX_AGREEMENT);
    }

    public function reviseAgreementNumberMonth(GrantNumbering $numbering): GrantNumbering
    {
        abort_if($numbering->tahun !== (int) now()->format('Y'), 422);

        $currentMonth = (int) now()->format('m');

        $newNumber = collect([
            $numbering->kode,
            $numbering->tahun,
            $this->romanMonth($currentMonth),
            $numbering->nomor_urut,
            $numbering->kode_satuan_kerja,
        ])->join('/');

        return DB::transaction(function () use ($numbering, $newNumber, $currentMonth) {
            $numbering->delete();

            return $numbering->grant->numberings()->create([
                'nomor' => $newNumber,
                'kode' => $numbering->kode,
                'nomor_urut' => $numbering->nomor_urut,
                'bulan' => $currentMonth,
                'tahun' => $numbering->tahun,
                'tahapan' => $numbering->tahapan->value,
                'kode_satuan_kerja' => $numbering->kode_satuan_kerja,
            ]);
        });
    }

    private function issueNumber(Grant $grant, string $prefix): GrantNumbering
    {
        $unit = $grant->orgUnit;
        $year = (int) now()->format('Y');
        $month = (int) now()->format('m');

        $lastSequence = GrantNumbering::query()
            ->where('kode', $prefix)
            ->where('tahun', $year)
            ->max('nomor_urut');

        $sequence = ($lastSequence ?? 0) + 1;

        $number = collect([
            $prefix,
            $year,
            $this->romanMonth($month),
            $sequence,
            $unit->kode,
        ])->join('/');

        $stage = $prefix === self::PREFIX_PLANNING
            ? GrantStage::Planning
            : GrantStage::Agreement;

        return $grant->numberings()->create([
            'nomor' => $number,
            'kode' => $prefix,
            'nomor_urut' => $sequence,
            'bulan' => $month,
            'tahun' => $year,
            'tahapan' => $stage->value,
            'kode_satuan_kerja' => $unit->kode,
        ]);
    }

    private function romanMonth(int $month): string
    {
        return match ($month) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        };
    }
}
