<?php

namespace Database\Seeders;

use App\Enums\AssessmentAspect;
use App\Enums\AssessmentResult;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Models\Grant;
use App\Models\GrantAssessmentResult;
use Illuminate\Database\Seeder;

/**
 * Completes Polda review for the demo grant (all 4 aspects fulfilled → PoldaVerifiedPlanning).
 *
 * Must be run after DemoGrantPlanningSeeder.
 */
class DemoPoldaReviewSeeder extends Seeder
{
    public function run(): void
    {
        $grant = Grant::where('nama_hibah', 'Pengadaan Peralatan Forensik Digital untuk Laboratorium Siber')->firstOrFail();
        $satkerUnit = $grant->orgUnit;
        $poldaUnit = $satkerUnit->parent;

        // 1. Status: Polda Reviewing
        $poldaReviewHistory = $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PlanningSubmittedToPolda->value,
            'status_sesudah' => GrantStatus::PoldaReviewingPlanning->value,
            'keterangan' => "{$poldaUnit->nama_unit} memulai kajian usulan hibah untuk kegiatan {$grant->nama_hibah}",
        ]);

        foreach (AssessmentAspect::cases() as $aspect) {
            $poldaAssessment = $poldaReviewHistory->assessments()->create([
                'judul' => $aspect->label(),
                'aspek' => $aspect->value,
                'tahapan' => GrantStage::Planning->value,
            ]);

            $resultModel = new GrantAssessmentResult([
                'rekomendasi' => AssessmentResult::Fulfilled->value,
                'keterangan' => null,
            ]);
            $resultModel->assessment()->associate($poldaAssessment);
            $resultModel->orgUnit()->associate($poldaUnit);
            $resultModel->save();
        }

        // 2. Status: Polda Verified → ready for Mabes review
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PoldaReviewingPlanning->value,
            'status_sesudah' => GrantStatus::PoldaVerifiedPlanning->value,
            'keterangan' => "Usulan hibah untuk kegiatan {$grant->nama_hibah} disetujui oleh Polda",
        ]);
    }
}
