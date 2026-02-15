<?php

namespace App\Enums;

enum AssessmentAspect: string
{
    case Technical = 'TEKNIS';
    case Economic = 'EKONOMIS';
    case Political = 'POLITIS';
    case Strategic = 'STRATEGIS';

    /**
     * @return string[]
     */
    public function prompts(): array
    {
        return match ($this) {
            self::Technical => [
                'Kesesuaian dengan kebutuhan dan dapat digunakan untuk kepentingan keamanan dalam negeri',
                'Kepastian bahwa objek hasil dari hibah sesuai dengan standar dan ketentuan yang berlaku di lingkungan Polri',
            ],
            self::Economic => [
                'Kemanfaatan yang diperoleh akan lebih besar daripada potensi beban penyelenggaraan, operasional, pemeliharaan dan perawatan yang akan timbul',
                'Sinergi antara hibah dengan DIPA Polri',
            ],
            self::Political => [
                'Dampak terhadap kemandirian serta kredibilitas Polri',
                'Potensi atas peningkatan kualitas hubungan bilateral antara Polri dan pemberi hibah',
            ],
            self::Strategic => [
                'Keselarasan dengan visi dan misi Polri',
                'Kapasitas untuk meningkatkan kemampuan dalam melaksanakan tugas dan fungsi kepolisian',
            ],
        };
    }
}
