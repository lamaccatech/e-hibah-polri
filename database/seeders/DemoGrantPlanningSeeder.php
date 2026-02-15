<?php

namespace Database\Seeders;

use App\Enums\AssessmentAspect;
use App\Enums\GrantForm;
use App\Enums\GrantStage;
use App\Enums\GrantStatus;
use App\Enums\GrantType;
use App\Models\Donor;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a fully submitted grant plan from POLRESTA BANDA ACEH → POLDA ACEH.
 *
 * Demo login:
 * - Satker: polrestabandaaceh640122@polri.go.id / password
 * - Polda:  poldaaceh@polri.go.id / password
 */
class DemoGrantPlanningSeeder extends Seeder
{
    public function run(): void
    {
        $satkerUser = User::find(54); // POLRESTA BANDA ACEH
        $satkerUnit = OrgUnit::where('id_user', $satkerUser->id)->first();

        // 1. Create donor
        $donor = Donor::create([
            'nama' => 'PT Pertamina (Persero)',
            'asal' => 'DALAM_NEGERI',
            'alamat' => 'Jl. Medan Merdeka Timur 1A, Jakarta Pusat',
            'negara' => 'ID',
            'kode_provinsi' => '11',
            'nama_provinsi' => 'ACEH',
            'kategori' => 'BUMN',
            'nomor_telepon' => '021-1500000',
            'email' => 'csr@pertamina.com',
        ]);

        // 2. Create grant
        $grant = $satkerUnit->grants()->create([
            'id_pemberi_hibah' => $donor->id,
            'nama_hibah' => 'Pengadaan Peralatan Forensik Digital untuk Laboratorium Siber',
            'jenis_hibah' => GrantType::Direct->value,
            'tahapan' => GrantStage::Planning->value,
            'bentuk_hibah' => GrantForm::Goods->value,
            'nilai_hibah' => 750000000,
            'mata_uang' => 'IDR',
            'ada_usulan' => true,
        ]);

        // 3. Status: Initialized
        $grant->statusHistory()->create([
            'status_sesudah' => GrantStatus::PlanningInitialized->value,
            'keterangan' => "{$satkerUnit->nama_unit} membuat usulan hibah untuk kegiatan {$grant->nama_hibah}",
        ]);

        // 4. Status: Filling Donor
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::PlanningInitialized->value,
            'status_sesudah' => GrantStatus::FillingDonorCandidate->value,
            'keterangan' => "{$satkerUnit->nama_unit} mengisi data calon pemberi hibah",
        ]);

        // 5. Status: Creating Proposal Document + Information chapters
        $proposalHistory = $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::FillingDonorCandidate->value,
            'status_sesudah' => GrantStatus::CreatingProposalDocument->value,
            'keterangan' => "{$satkerUnit->nama_unit} membuat naskah usulan hibah untuk kegiatan {$grant->nama_hibah}",
        ]);

        $chapters = [
            ['judul' => 'Latar Belakang', 'isi' => '<p>Dalam rangka peningkatan kapabilitas penanganan kejahatan siber di wilayah hukum Polresta Banda Aceh, diperlukan peralatan forensik digital yang memadai. Saat ini peralatan yang tersedia sudah tidak mampu mengimbangi perkembangan teknologi yang digunakan pelaku kejahatan.</p>'],
            ['judul' => 'Maksud dan Tujuan', 'isi' => '<p>Pengadaan peralatan forensik digital bertujuan untuk meningkatkan kemampuan penyidik dalam mengungkap kasus kejahatan siber, pemalsuan dokumen digital, dan kejahatan berbasis teknologi informasi lainnya.</p>'],
            ['judul' => 'Ruang Lingkup', 'isi' => '<p>Hibah mencakup pengadaan seperangkat peralatan forensik digital meliputi: 1 unit Cellebrite UFED Touch, 2 unit write blocker, 3 unit hard disk forensik, serta software analisis forensik berlisensi.</p>'],
        ];

        foreach ($chapters as $index => $chapter) {
            $info = $grant->information()->create([
                'judul' => $chapter['judul'],
                'tahapan' => GrantStage::Planning->value,
            ]);

            $info->contents()->create([
                'subjudul' => '',
                'isi' => $chapter['isi'],
                'nomor_urut' => $index + 1,
            ]);
        }

        // 6. Budget plans
        $budgetItems = [
            ['uraian' => 'Cellebrite UFED Touch', 'nilai' => 350000000],
            ['uraian' => 'Write Blocker (2 unit)', 'nilai' => 60000000],
            ['uraian' => 'Hard Disk Forensik (3 unit)', 'nilai' => 45000000],
            ['uraian' => 'Software Analisis Forensik', 'nilai' => 200000000],
            ['uraian' => 'Pelatihan Operator', 'nilai' => 95000000],
        ];

        foreach ($budgetItems as $index => $item) {
            $grant->budgetPlans()->create([
                'nomor_urut' => $index + 1,
                'uraian' => $item['uraian'],
                'nilai' => $item['nilai'],
            ]);
        }

        // 7. Activity schedules
        $grant->activitySchedules()->create([
            'uraian_kegiatan' => 'Pengadaan dan pengiriman peralatan',
            'tanggal_mulai' => '2026-04-01',
            'tanggal_selesai' => '2026-06-30',
        ]);

        $grant->activitySchedules()->create([
            'uraian_kegiatan' => 'Instalasi dan pelatihan',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-08-31',
        ]);

        // 8. Status: Creating Assessment + 4 mandatory aspect assessments
        $assessmentHistory = $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::CreatingProposalDocument->value,
            'status_sesudah' => GrantStatus::CreatingPlanningAssessment->value,
            'keterangan' => "{$satkerUnit->nama_unit} membuat dokumen kajian usulan hibah untuk kegiatan {$grant->nama_hibah}",
        ]);

        $assessmentContents = [
            AssessmentAspect::Technical->value => [
                ['subjudul' => 'Kesesuaian dengan kebutuhan dan dapat digunakan untuk kepentingan keamanan dalam negeri', 'isi' => '<p>Peralatan forensik digital sangat dibutuhkan untuk mendukung penyidikan kasus kejahatan siber yang terus meningkat. Peralatan ini akan digunakan untuk kepentingan keamanan dalam negeri khususnya penegakan hukum di bidang teknologi informasi.</p>'],
                ['subjudul' => 'Kepastian bahwa objek hasil dari hibah sesuai dengan standar dan ketentuan yang berlaku di lingkungan Polri', 'isi' => '<p>Seluruh peralatan yang diusulkan telah sesuai dengan standar yang ditetapkan oleh Puslabfor Polri dan memenuhi ketentuan teknis laboratorium forensik digital Polri.</p>'],
            ],
            AssessmentAspect::Economic->value => [
                ['subjudul' => 'Kemanfaatan yang diperoleh akan lebih besar daripada potensi beban penyelenggaraan, operasional, pemeliharaan dan perawatan yang akan timbul', 'isi' => '<p>Manfaat peralatan forensik digital jauh melebihi biaya operasional dan pemeliharaan. Peralatan ini akan meningkatkan kemampuan pengungkapan kasus yang berdampak pada keamanan dan ketertiban masyarakat.</p>'],
                ['subjudul' => 'Sinergi antara hibah dengan DIPA Polri', 'isi' => '<p>Pengadaan ini melengkapi alokasi DIPA Polri yang sudah dianggarkan untuk pengembangan kapabilitas siber Polresta Banda Aceh tahun anggaran 2026.</p>'],
            ],
            AssessmentAspect::Political->value => [
                ['subjudul' => 'Dampak terhadap kemandirian serta kredibilitas Polri', 'isi' => '<p>Penerimaan hibah dari BUMN nasional justru memperkuat kemandirian Polri karena menunjukkan dukungan korporasi dalam negeri terhadap penegakan hukum, tanpa ketergantungan pada pihak asing.</p>'],
                ['subjudul' => 'Potensi atas peningkatan kualitas hubungan bilateral antara Polri dan pemberi hibah', 'isi' => '<p>Kerjasama ini berpotensi meningkatkan hubungan strategis antara Polri dan PT Pertamina dalam bidang keamanan siber dan perlindungan infrastruktur kritis nasional.</p>'],
            ],
            AssessmentAspect::Strategic->value => [
                ['subjudul' => 'Keselarasan dengan visi dan misi Polri', 'isi' => '<p>Pengadaan peralatan forensik digital selaras dengan visi Polri sebagai institusi penegak hukum yang profesional, modern, dan terpercaya. Hal ini juga mendukung misi peningkatan pelayanan kepolisian berbasis teknologi.</p>'],
                ['subjudul' => 'Kapasitas untuk meningkatkan kemampuan dalam melaksanakan tugas dan fungsi kepolisian', 'isi' => '<p>Peralatan ini akan secara langsung meningkatkan kapasitas Polresta Banda Aceh dalam menjalankan fungsi penyidikan kejahatan siber dan digital evidence handling.</p>'],
            ],
        ];

        foreach (AssessmentAspect::cases() as $aspect) {
            $assessment = $assessmentHistory->assessments()->create([
                'judul' => $aspect->label(),
                'aspek' => $aspect->value,
                'tahapan' => GrantStage::Planning->value,
            ]);

            foreach ($assessmentContents[$aspect->value] as $index => $content) {
                $assessment->contents()->create([
                    'subjudul' => $content['subjudul'],
                    'isi' => $content['isi'],
                    'nomor_urut' => $index + 1,
                ]);
            }
        }

        // 9. Status: Submitted to Polda
        $grant->statusHistory()->create([
            'status_sebelum' => GrantStatus::CreatingPlanningAssessment->value,
            'status_sesudah' => GrantStatus::PlanningSubmittedToPolda->value,
            'keterangan' => "{$satkerUnit->nama_unit} mengajukan usulan hibah untuk kegiatan {$grant->nama_hibah}",
        ]);
    }
}
