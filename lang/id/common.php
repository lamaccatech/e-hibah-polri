<?php

return [

    // Actions
    'save' => 'Simpan',
    'cancel' => 'Batal',
    'delete' => 'Hapus',
    'edit' => 'Edit',
    'back' => 'Kembali',
    'continue' => 'Lanjutkan',
    'confirm' => 'Konfirmasi',
    'close' => 'Tutup',
    'create' => 'Buat',
    'logout' => 'Keluar',

    // Status
    'saved' => 'Tersimpan.',

    // Navigation
    'dashboard' => 'Dasbor',
    'settings' => 'Pengaturan',
    'search' => 'Pencarian',
    'repository' => 'Repositori',
    'documentation' => 'Dokumentasi',

    // Labels
    'email' => 'Email',
    'password' => 'Password',
    'file-format-image' => 'JPG, PNG',

    // Grant stages
    'planning' => 'Usulan',
    'agreement' => 'Perjanjian',

    // Enum: GrantType
    'grant-type' => [
        'direct' => 'Langsung',
        'planned' => 'Terencana',
    ],

    // Enum: GrantForm
    'grant-form' => [
        'money' => 'Uang',
        'goods' => 'Barang',
        'services' => 'Jasa',
    ],

    // Enum: UnitLevel
    'unit-level' => [
        'mabes' => 'Mabes',
        'satuan-induk' => 'Satuan Induk',
        'satuan-kerja' => 'Satuan Kerja',
    ],

    // Enum: FileType
    'file-type' => [
        'attachment' => 'Lampiran',
        'draft-agreement' => 'Draft Perjanjian',
        'agreement' => 'Naskah Perjanjian',
        'donor-letter' => 'Surat Pemberi Hibah',
        'signature' => 'Tanda Tangan',
        'generated-document' => 'Dokumen Hasil Cetak',
    ],

    // Enum: LogAction
    'log-action' => [
        'create' => 'Membuat',
        'update' => 'Mengubah',
        'delete' => 'Menghapus',
        'login' => 'Masuk',
        'logout' => 'Keluar',
        'submit' => 'Mengajukan',
        'review' => 'Mengkaji',
        'verify' => 'Memverifikasi',
        'reject' => 'Menolak',
        'request-revision' => 'Meminta Revisi',
    ],

    // Enum: AssessmentAspect
    'assessment-aspect' => [
        'technical' => 'Teknis',
        'economic' => 'Ekonomis',
        'political' => 'Politis',
        'strategic' => 'Strategis',
        'prompt-technical-1' => 'Kesesuaian dengan kebutuhan dan dapat digunakan untuk kepentingan keamanan dalam negeri',
        'prompt-technical-2' => 'Kepastian bahwa objek hasil dari hibah sesuai dengan standar dan ketentuan yang berlaku di lingkungan Polri',
        'prompt-economic-1' => 'Kemanfaatan yang diperoleh akan lebih besar daripada potensi beban penyelenggaraan, operasional, pemeliharaan dan perawatan yang akan timbul',
        'prompt-economic-2' => 'Sinergi antara hibah dengan DIPA Polri',
        'prompt-political-1' => 'Dampak terhadap kemandirian serta kredibilitas Polri',
        'prompt-political-2' => 'Potensi atas peningkatan kualitas hubungan bilateral antara Polri dan pemberi hibah',
        'prompt-strategic-1' => 'Keselarasan dengan visi dan misi Polri',
        'prompt-strategic-2' => 'Kapasitas untuk meningkatkan kemampuan dalam melaksanakan tugas dan fungsi kepolisian',
    ],

    // Enum: ProposalChapter
    'proposal-chapter' => [
        'general' => 'Umum',
        'purpose' => 'Maksud',
        'objective' => 'Tujuan',
        'target' => 'Sasaran Kegiatan',
        'benefit' => 'Manfaat Kegiatan',
        'implementation-plan' => 'Rencana Pelaksanaan Kegiatan',
        'budget-plan' => 'Rencana Kebutuhan Anggaran Kegiatan',
        'reporting-plan' => 'Rencana Pelaporan',
        'evaluation-plan' => 'Rencana Evaluasi',
        'closing' => 'Penutup',
        'reception-basis' => 'Dasar Penerimaan Hibah',
        'supervision-mechanism' => 'Mekanisme Pengawasan Hibah',
        'prompt-general-1' => 'Jelaskan latar belakang kebutuhan',
        'prompt-general-2' => 'Jelaskan informasi relevan untuk mendukung urgensi kegiatan',
        'prompt-general-3' => 'Uraikan bagaimana kegiatan dapat memberikan solusi',
        'prompt-target-1' => 'Jelaskan tentang sasaran atas indikator-indikator yang dapat ditingkatkan untuk pencapaian tujuan',
        'prompt-benefit-1' => 'Jelaskan manfaat kegiatan yang dapat mendukung tugas Polri dan Masyarakat',
        'prompt-benefit-2' => 'Jelaskan dampak, baik langsung maupun tidak langsung, kepada calon pemberi hibah',
        'prompt-implementation-1' => 'Jelaskan langkah-langkah yang akan dilaksanakan',
        'prompt-reporting-1' => 'Jelaskan rencana pelaporan kepada calon pemberi hibah',
        'prompt-evaluation-1' => 'Jelaskan rencana evaluasi yang akan dilakukan oleh Polri',
    ],

    // Enum: GrantGeneratedDocumentType
    'grant-generated-document-type' => [
        'assessment-document' => 'Kajian Usulan',
        'proposal-document' => 'Naskah Usulan',
        'readiness-document' => 'Kesiapan Penerimaan Hibah Langsung',
        'filename-assessment' => 'Kajian Usulan',
        'filename-proposal' => 'Naskah Usulan',
        'filename-readiness' => 'Kesiapan Hibah',
    ],

];
