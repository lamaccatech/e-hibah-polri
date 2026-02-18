<?php

return [

    // Status history keterangan messages
    'status-history' => [
        // Satker planning actions
        'planning-initialized' => ':unit memulai pembuatan naskah usulan hibah dalam rangka kegiatan :activity',
        'filling-donor-candidate' => ':unit mengisi data calon pemberi hibah :donor untuk kegiatan :activity',
        'creating-proposal-document' => ':unit membuat naskah usulan hibah untuk kegiatan :activity',
        'creating-planning-assessment' => ':unit membuat dokumen kajian usulan hibah untuk kegiatan :activity',
        'planning-submitted' => ':unit mengajukan usulan hibah untuk kegiatan :activity',

        // Satker agreement actions
        'filling-reception-data' => ':unit mengisi data dasar penerimaan hibah dalam rangka kegiatan :activity',
        'filling-donor-info' => ':unit mengisi data pemberi hibah :donor untuk kegiatan :activity',
        'advance-to-donor-info' => ':unit melanjutkan ke langkah data pemberi hibah untuk kegiatan :activity',
        'creating-agreement-assessment' => ':unit membuat dokumen kajian hibah untuk kegiatan :activity',
        'filling-harmonization' => ':unit mengisi data harmonisasi naskah perjanjian hibah untuk kegiatan :activity',
        'filling-additional-materials' => ':unit mengisi materi tambahan kesiapan hibah untuk kegiatan :activity',
        'filling-other-materials' => ':unit mengisi materi kesiapan hibah lainnya untuk kegiatan :activity',
        'skipping-other-materials' => ':unit melewati langkah materi kesiapan hibah lainnya untuk kegiatan :activity',
        'uploading-draft-agreement' => ':unit mengupload draft naskah perjanjian hibah untuk kegiatan :activity',
        'agreement-submitted' => ':unit mengajukan perjanjian hibah untuk kegiatan :activity',
        'uploading-signed-agreement' => ':unit mengupload naskah perjanjian hibah untuk kegiatan :activity',
        'sehati-submitted' => ':unit mengisi data SEHATI/Kemenkeu untuk kegiatan :activity',

        // Review actions (shared by Polda and Mabes)
        'start-planning-review' => ':unit memulai kajian usulan hibah untuk kegiatan :activity',
        'start-agreement-review' => ':unit memulai kajian perjanjian hibah untuk kegiatan :activity',

        // Review outcomes (parameterized with :reviewer for Polda/Mabes)
        'planning-rejected' => 'Usulan hibah untuk kegiatan :activity ditolak oleh :reviewer',
        'planning-revision-requested' => ':reviewer meminta revisi untuk usulan hibah kegiatan :activity',
        'planning-verified' => 'Usulan hibah untuk kegiatan :activity disetujui oleh :reviewer',
        'agreement-rejected' => 'Perjanjian hibah untuk kegiatan :activity ditolak oleh :reviewer',
        'agreement-revision-requested' => ':reviewer meminta revisi untuk perjanjian hibah kegiatan :activity',
        'agreement-verified' => 'Perjanjian hibah untuk kegiatan :activity disetujui oleh :reviewer',
        'planning-number-issued' => 'Nomor usulan hibah terbit untuk kegiatan :activity',
        'agreement-number-issued' => 'Nomor naskah perjanjian terbit untuk kegiatan :activity',
    ],

    // Activity log messages
    'activity-log' => [
        'create-planning' => 'Membuat usulan hibah baru',
        'create-donor' => 'Membuat data pemberi hibah baru',
        'create-agreement' => 'Membuat perjanjian hibah baru',
        'submit-planning' => 'Mengajukan usulan hibah: :activity',
        'submit-agreement' => 'Mengajukan perjanjian hibah: :activity',
        'start-planning-review' => 'Memulai kajian usulan hibah: :activity',
        'start-agreement-review' => 'Memulai kajian perjanjian hibah: :activity',
        'resolve-planning' => ':action usulan hibah: :activity',
        'resolve-agreement' => ':action perjanjian hibah: :activity',
    ],

    // Model labels for User::buildMessage
    'model-label' => [
        'grant' => 'hibah',
        'donor' => 'pemberi hibah',
        'org-unit' => 'unit',
        'org-unit-chief' => 'kepala unit',
        'user' => 'pengguna',
    ],

    // User::buildMessage action labels
    'action' => [
        'create' => 'Membuat',
        'update' => 'Mengubah',
        'delete' => 'Menghapus',
    ],

    // Auto-generated content
    'generated-content' => [
        'purpose-prefix' => 'Kegiatan :activity',
        'purpose-objective' => ' bertujuan untuk :objectives',
    ],

];
