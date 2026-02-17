<?php

return [

    // Login
    'login' => [
        'title' => 'Masuk ke akun Anda',
        'description' => 'Masukkan email dan password Anda untuk masuk',
        'label-email' => 'Alamat email',
        'placeholder-email' => 'email@contoh.com',
        'forgot-password' => 'Lupa password?',
        'remember-me' => 'Ingat saya',
        'submit-button' => 'Masuk',
    ],

    // Forgot Password
    'forgot-password' => [
        'title' => 'Lupa password',
        'description' => 'Masukkan email Anda untuk menerima tautan atur ulang password',
        'submit-button' => 'Kirim tautan atur ulang password',
        'return-text' => 'Atau, kembali ke',
        'login-link' => 'halaman masuk',
    ],

    // Reset Password
    'reset-password' => [
        'title' => 'Atur ulang password',
        'description' => 'Silakan masukkan password baru Anda di bawah ini',
        'label-confirm-password' => 'Konfirmasi password',
        'submit-button' => 'Atur ulang password',
    ],

    // Two-Factor Challenge
    'two-factor-challenge' => [
        'title-code' => 'Kode Autentikasi',
        'description-code' => 'Masukkan kode autentikasi yang disediakan oleh aplikasi autentikator Anda.',
        'title-recovery' => 'Kode Pemulihan',
        'description-recovery' => 'Silakan konfirmasi akses ke akun Anda dengan memasukkan salah satu kode pemulihan darurat.',
        'label-otp' => 'Kode OTP',
        'toggle-prefix' => 'atau Anda dapat',
        'toggle-to-recovery' => 'masuk menggunakan kode pemulihan',
        'toggle-to-code' => 'masuk menggunakan kode autentikasi',
    ],

    // Confirm Password
    'confirm-password' => [
        'title' => 'Konfirmasi password',
        'description' => 'Ini adalah area aman dari aplikasi. Silakan konfirmasi password Anda sebelum melanjutkan.',
    ],

    // Verify Email
    'verify-email' => [
        'description' => 'Silakan verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan.',
        'success' => 'Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.',
        'resend-button' => 'Kirim ulang email verifikasi',
    ],

    // Profile Settings
    'profile' => [
        'sr-title' => 'Pengaturan Profil',
        'title' => 'Profil',
        'description' => 'Perbarui nama dan alamat email Anda',
        'label-name' => 'Nama',
        'unverified-email' => 'Alamat email Anda belum diverifikasi.',
        'resend-link' => 'Klik di sini untuk mengirim ulang email verifikasi.',
        'resend-success' => 'Tautan verifikasi baru telah dikirim ke alamat email Anda.',
    ],

    // Password Settings
    'password' => [
        'sr-title' => 'Pengaturan Password',
        'title' => 'Ubah password',
        'description' => 'Pastikan akun Anda menggunakan password yang panjang dan acak agar tetap aman',
        'label-current' => 'Password saat ini',
        'label-new' => 'Password baru',
        'label-confirm' => 'Konfirmasi Password',
    ],

    // Two-Factor Settings
    'two-factor' => [
        'sr-title' => 'Pengaturan Autentikasi Dua Faktor',
        'title' => 'Autentikasi Dua Faktor',
        'description' => 'Kelola pengaturan autentikasi dua faktor Anda',
        'badge-enabled' => 'Aktif',
        'badge-disabled' => 'Nonaktif',
        'enabled-description' => 'Dengan autentikasi dua faktor aktif, Anda akan diminta memasukkan PIN acak yang aman saat login, yang dapat Anda ambil dari aplikasi yang mendukung TOTP di ponsel Anda.',
        'disabled-description' => 'Saat Anda mengaktifkan autentikasi dua faktor, Anda akan diminta memasukkan PIN yang aman saat login. PIN ini dapat diambil dari aplikasi yang mendukung TOTP di ponsel Anda.',
        'button-disable' => 'Nonaktifkan 2FA',
        'button-enable' => 'Aktifkan 2FA',
        'manual-entry-label' => 'atau, masukkan kode secara manual',
        'modal-enabled-title' => 'Autentikasi Dua Faktor Aktif',
        'modal-enabled-description' => 'Autentikasi dua faktor sekarang aktif. Pindai kode QR atau masukkan kunci pengaturan di aplikasi autentikator Anda.',
        'modal-verify-title' => 'Verifikasi Kode Autentikasi',
        'modal-verify-description' => 'Masukkan kode 6 digit dari aplikasi autentikator Anda.',
        'modal-setup-title' => 'Aktifkan Autentikasi Dua Faktor',
        'modal-setup-description' => 'Untuk menyelesaikan pengaktifan autentikasi dua faktor, pindai kode QR atau masukkan kunci pengaturan di aplikasi autentikator Anda.',
        'error-fetch-setup' => 'Gagal mengambil data pengaturan.',
    ],

    // Recovery Codes
    'recovery-codes' => [
        'title' => 'Kode Pemulihan 2FA',
        'description' => 'Kode pemulihan memungkinkan Anda mendapatkan kembali akses jika perangkat 2FA Anda hilang. Simpan di pengelola password yang aman.',
        'button-view' => 'Lihat Kode Pemulihan',
        'button-hide' => 'Sembunyikan Kode Pemulihan',
        'button-regenerate' => 'Buat Ulang Kode',
        'aria-label' => 'Kode pemulihan',
        'usage-note' => 'Setiap kode pemulihan hanya dapat digunakan sekali untuk mengakses akun Anda dan akan dihapus setelah digunakan. Jika Anda membutuhkan lebih banyak, klik Buat Ulang Kode di atas.',
        'error-load' => 'Gagal memuat kode pemulihan',
    ],

    // Appearance Settings
    'appearance' => [
        'sr-title' => 'Pengaturan Tampilan',
        'title' => 'Tampilan',
        'description' => 'Perbarui pengaturan tampilan untuk akun Anda',
        'light' => 'Terang',
        'dark' => 'Gelap',
        'system' => 'Sistem',
    ],

    // Delete Account
    'delete-account' => [
        'title' => 'Hapus akun',
        'description' => 'Hapus akun Anda dan semua sumber dayanya',
        'modal-title' => 'Apakah Anda yakin ingin menghapus akun Anda?',
        'modal-description' => 'Setelah akun Anda dihapus, semua sumber daya dan data akan dihapus secara permanen. Silakan masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun secara permanen.',
    ],

    // User Management
    'user-management' => [
        'title' => 'Manajemen User',
        'create-button' => 'Tambah User',
        'column-unit-name' => 'Nama Unit',
        'column-code' => 'Kode',
        'column-level' => 'Level',
        'column-action' => 'Aksi',
        'search-placeholder' => 'Cari nama unit...',
        'empty-state' => 'Belum ada user.',
        'delete-modal-title' => 'Hapus User',
        'delete-modal-description' => 'Hapus user dan unit ini? Tindakan ini tidak dapat dibatalkan.',
        'error-has-grants' => 'User tidak dapat dihapus karena memiliki hibah aktif',
        'error-has-subordinates' => 'User tidak dapat dihapus karena memiliki unit bawahan',
    ],

    // User Create
    'user-create' => [
        'title' => 'Tambah User',
        'label-password-confirmation' => 'Konfirmasi Password',
        'label-unit-name' => 'Nama Unit',
        'label-unit-code' => 'Kode Unit',
        'label-unit-level' => 'Level Unit',
        'placeholder-level' => 'Pilih level...',
        'label-parent-unit' => 'Unit Atasan',
        'placeholder-parent-unit' => 'Pilih unit atasan...',
    ],

    // User Edit
    'user-edit' => [
        'title' => 'Edit User',
        'label-unit-name' => 'Nama Unit',
        'label-unit-code' => 'Kode Unit',
    ],

    // Chief Management
    'chief-management' => [
        'title' => 'Manajemen Kepala Satker',
        'create-button' => 'Tambah Kepala',
        'column-name' => 'Nama Lengkap',
        'column-position' => 'Jabatan',
        'column-rank' => 'Pangkat',
        'column-nrp' => 'NRP',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'badge-active' => 'Menjabat',
        'badge-inactive' => 'Tidak Menjabat',
        'empty-state' => 'Belum ada kepala satker.',
    ],

    // Chief Create
    'chief-create' => [
        'title' => 'Tambah Kepala Satker',
        'label-name' => 'Nama Lengkap',
        'label-position' => 'Jabatan',
        'label-rank' => 'Pangkat',
        'label-nrp' => 'NRP',
        'label-signature' => 'Tanda Tangan',
        'signature-preview' => 'Pratinjau tanda tangan',
    ],

    // Chief Edit
    'chief-edit' => [
        'title' => 'Edit Kepala Satker',
        'label-name' => 'Nama Lengkap',
        'label-position' => 'Jabatan',
        'label-rank' => 'Pangkat',
        'label-nrp' => 'NRP',
        'label-signature' => 'Tanda Tangan Baru',
        'current-signature' => 'Tanda tangan saat ini',
        'signature-preview' => 'Pratinjau tanda tangan baru',
    ],

    // Dashboard
    'dashboard' => [
        'title-grant-type' => 'Pilih Jenis Hibah',
        'direct-grant-title' => 'Hibah Langsung (HL)',
        'direct-grant-description' => 'Hibah yang diterima secara langsung, baik melalui proses usulan maupun langsung perjanjian.',
        'planned-grant-title' => 'Hibah Yang Direncanakan (HDR)',
        'planned-grant-description' => 'Hibah yang direncanakan melalui proses perencanaan jangka panjang.',
        'planned-grant-badge' => 'Segera Hadir',
        'title-direct-options' => 'Hibah Langsung (HL)',
        'proposal-title' => 'Input Usulan',
        'proposal-description' => 'Buat dan kelola naskah usulan hibah melalui proses pengajuan ke Polda.',
        'agreement-title' => 'Input Perjanjian',
        'agreement-description' => 'Kelola perjanjian hibah langsung yang diterima tanpa proses usulan.',
        'agreement-badge' => 'Segera Hadir',
        'polda-title' => 'Dashboard Satuan Induk',
        'polda-description' => 'Statistik dan ringkasan hibah akan ditampilkan di sini.',
        'mabes-title' => 'Dashboard Mabes',
        'mabes-description' => 'Statistik dan ringkasan hibah akan ditampilkan di sini.',
        'coming-soon' => 'Segera Hadir',
    ],

    // Grant Planning — Index
    'grant-planning' => [
        'title' => 'Usulan Hibah',
        'create-button' => 'Buat Usulan',
        'column-name' => 'Nama Kegiatan',
        'column-donor' => 'Calon Pemberi Hibah',
        'column-status' => 'Status',
        'column-value' => 'Nilai',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada usulan hibah.',
        'submit-button' => 'Ajukan ke Polda',
        'submit-incomplete' => 'Lengkapi semua langkah sebelum mengajukan.',
        'badge-initialized' => 'Inisialisasi',
        'badge-filling-donor' => 'Data Pemberi Hibah',
        'badge-creating-proposal' => 'Naskah Usulan',
        'badge-creating-assessment' => 'Dokumen Kajian',
        'badge-submitted' => 'Diajukan',
        'badge-revising' => 'Revisi',
        'badge-revision-resubmitted' => 'Revisi Diajukan',
        'submit-confirm-title' => 'Ajukan Usulan Hibah',
        'submit-confirm-description' => 'Yakin ingin mengajukan usulan hibah ini ke Polda?',
    ],

    // Grant Planning — Initialize (Step 1)
    'grant-planning-create' => [
        'title' => 'Naskah Usulan Hibah',
        'label-activity-name' => 'Nama kegiatan yang akan dibiayai oleh hibah',
        'placeholder-activity-name' => 'Masukkan nama kegiatan...',
    ],

    // Grant Planning — Donor Info (Step 2)
    'grant-planning-donor' => [
        'title' => 'Formulir Calon Pemberi Hibah',
        'label-name' => 'Nama Pemberi',
        'placeholder-name' => 'Ketik nama pemberi hibah...',
        'label-origin' => 'Asal Pemberi Hibah',
        'label-address' => 'Alamat Pemberi Hibah',
        'label-country' => 'Negara',
        'placeholder-country' => 'Pilih negara...',
        'label-province' => 'Provinsi',
        'placeholder-province' => 'Pilih provinsi...',
        'label-regency' => 'Kabupaten/Kota',
        'placeholder-regency' => 'Pilih kabupaten/kota...',
        'label-district' => 'Kecamatan',
        'placeholder-district' => 'Pilih kecamatan...',
        'label-village' => 'Kelurahan/Desa',
        'placeholder-village' => 'Pilih kelurahan/desa...',
        'label-category' => 'Kategori',
        'placeholder-category' => 'Pilih kategori...',
        'label-phone' => 'Nomor Telp/Faks',
        'label-email' => 'Email Pemberi Hibah',
    ],

    // Grant Planning — Proposal Document (Step 3)
    'grant-planning-proposal' => [
        'title' => 'Naskah Usulan Hibah',
        'section-chapters' => 'Bab-bab Naskah',
        'section-budget' => 'Rencana Anggaran Biaya',
        'section-schedule' => 'Timeline Kegiatan',
        'placeholder-purpose' => 'Pilih tujuan...',
        'add-objective' => 'Tambah Tujuan',
        'label-currency' => 'Mata Uang',
        'add-budget-item' => 'Tambah Item Anggaran',
        'add-schedule' => 'Tambah Jadwal',
        'label-description' => 'Uraian',
        'label-value' => 'Nilai',
        'label-activity' => 'Uraian Kegiatan',
        'label-start-date' => 'Tanggal Mulai',
        'label-end-date' => 'Tanggal Selesai',
        'section-custom-chapters' => 'Bab Tambahan',
        'add-custom-chapter' => 'Tambah Bab',
        'add-paragraph' => 'Tambah Paragraf',
        'label-chapter-title' => 'Judul Bab',
    ],

    // Grant Planning — Assessment (Step 4)
    'grant-planning-assessment' => [
        'title' => 'Dokumen Kajian Usulan Hibah',
        'section-mandatory' => 'Aspek Wajib',
        'section-custom' => 'Aspek Tambahan',
        'add-custom-aspect' => 'Tambah Aspek',
        'add-paragraph' => 'Tambah Paragraf',
        'label-aspect-title' => 'Judul Aspek',
        'feedback-fulfilled' => 'Aspek ini telah disetujui oleh pengkaji.',
        'feedback-revision' => 'Aspek ini perlu direvisi.',
    ],

    // Grant Review — Polda
    'grant-review' => [
        'title' => 'Usulan Hibah Masuk',
        'column-unit' => 'Satuan Kerja',
        'column-name' => 'Nama Kegiatan',
        'column-donor' => 'Calon Pemberi Hibah',
        'column-value' => 'Nilai',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada usulan hibah masuk.',
        'badge-submitted' => 'Diajukan',
        'badge-reviewing' => 'Sedang Dikaji',
        'badge-verified' => 'Disetujui Polda',
        'badge-rejected' => 'Ditolak',
        'badge-revision-requested' => 'Perlu Revisi',
        'start-review-button' => 'Mulai Kajian',
        'start-review-confirm-title' => 'Mulai Kajian Usulan Hibah',
        'start-review-confirm-description' => 'Yakin ingin memulai kajian untuk usulan hibah ini?',
        'start-review-success' => 'Kajian berhasil dimulai.',
        'continue-review-button' => 'Lanjut Kajian',
        'review-title' => 'Kajian Usulan Hibah',
        'submit-result-button' => 'Berikan Penilaian',
        'result-label' => 'Penilaian',
        'result-remarks-label' => 'Keterangan',
        'result-fulfilled' => 'Terpenuhi',
        'result-revision' => 'Revisi',
        'result-rejected' => 'Ditolak',
        'result-saved' => 'Penilaian berhasil disimpan.',
        'satker-assessment-heading' => 'Kajian Satuan Kerja',
    ],

    // Agreement Review — Polda
    'agreement-review' => [
        'title' => 'Perjanjian Hibah Masuk',
        'column-unit' => 'Satuan Kerja',
        'column-name' => 'Nama Kegiatan',
        'column-donor' => 'Pemberi Hibah',
        'column-value' => 'Nilai',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada perjanjian hibah masuk.',
        'badge-submitted' => 'Diajukan',
        'badge-reviewing' => 'Sedang Dikaji',
        'badge-verified' => 'Disetujui Polda',
        'badge-rejected' => 'Ditolak',
        'badge-revision-requested' => 'Perlu Revisi',
        'start-review-button' => 'Mulai Kajian',
        'start-review-confirm-title' => 'Mulai Kajian Perjanjian Hibah',
        'start-review-confirm-description' => 'Yakin ingin memulai kajian untuk perjanjian hibah ini?',
        'start-review-success' => 'Kajian berhasil dimulai.',
        'continue-review-button' => 'Lanjut Kajian',
        'review-title' => 'Kajian Perjanjian Hibah',
        'submit-result-button' => 'Berikan Penilaian',
        'result-label' => 'Penilaian',
        'result-remarks-label' => 'Keterangan',
        'result-fulfilled' => 'Terpenuhi',
        'result-revision' => 'Revisi',
        'result-rejected' => 'Ditolak',
        'result-saved' => 'Penilaian berhasil disimpan.',
        'satker-assessment-heading' => 'Kajian Satuan Kerja',
    ],

    // Grant Review — Mabes
    'mabes-grant-review' => [
        'title' => 'Usulan Hibah Masuk',
        'column-unit' => 'Satuan Kerja',
        'column-polda' => 'Satuan Induk',
        'column-name' => 'Nama Kegiatan',
        'column-donor' => 'Calon Pemberi Hibah',
        'column-value' => 'Nilai',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada usulan hibah masuk.',
        'badge-reviewing' => 'Sedang Dikaji Mabes',
        'badge-verified' => 'Disetujui Mabes',
        'badge-rejected' => 'Ditolak Mabes',
        'badge-revision-requested' => 'Perlu Revisi Mabes',
        'badge-number-issued' => 'Nomor Terbit',
        'start-review-button' => 'Mulai Kajian',
        'start-review-confirm-title' => 'Mulai Kajian Usulan Hibah',
        'start-review-confirm-description' => 'Yakin ingin memulai kajian untuk usulan hibah ini?',
        'continue-review-button' => 'Lanjut Kajian',
        'review-title' => 'Kajian Usulan Hibah',
        'submit-result-button' => 'Berikan Penilaian',
        'result-label' => 'Penilaian',
        'result-remarks-label' => 'Keterangan',
        'result-fulfilled' => 'Terpenuhi',
        'result-revision' => 'Revisi',
        'result-rejected' => 'Ditolak',
        'result-saved' => 'Penilaian berhasil disimpan.',
        'satker-assessment-heading' => 'Kajian Satuan Kerja',
        'polda-assessment-heading' => 'Kajian Satuan Induk',
    ],

    // Mabes Agreement Review
    'mabes-agreement-review' => [
        'title' => 'Perjanjian Hibah Masuk',
        'column-unit' => 'Satuan Kerja',
        'column-polda' => 'Satuan Induk',
        'column-name' => 'Nama Kegiatan',
        'column-donor' => 'Pemberi Hibah',
        'column-value' => 'Nilai',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada perjanjian hibah masuk.',
        'badge-reviewing' => 'Sedang Dikaji Mabes',
        'badge-verified' => 'Disetujui Mabes',
        'badge-rejected' => 'Ditolak Mabes',
        'badge-revision-requested' => 'Perlu Revisi Mabes',
        'badge-number-issued' => 'Nomor Terbit',
        'start-review-button' => 'Mulai Kajian',
        'start-review-confirm-title' => 'Mulai Kajian Perjanjian Hibah',
        'start-review-confirm-description' => 'Yakin ingin memulai kajian untuk perjanjian hibah ini?',
        'continue-review-button' => 'Lanjut Kajian',
        'review-title' => 'Kajian Perjanjian Hibah',
        'submit-result-button' => 'Berikan Penilaian',
        'result-label' => 'Penilaian',
        'result-remarks-label' => 'Keterangan',
        'result-fulfilled' => 'Terpenuhi',
        'result-revision' => 'Revisi',
        'result-rejected' => 'Ditolak',
        'result-saved' => 'Penilaian berhasil disimpan.',
        'satker-assessment-heading' => 'Kajian Satuan Kerja',
        'polda-assessment-heading' => 'Kajian Satuan Induk',
        'clear-tag' => 'Hapus Kategori',
        'no-tags' => 'Belum ada kategori.',
    ],

    // Grant Detail
    'grant-detail' => [
        'title' => 'Detail Hibah',
        'tab-grant-info' => 'Informasi Hibah',
        'tab-proposal-info' => 'Naskah Usulan',
        'tab-assessment-info' => 'Kajian Usulan',
        'tab-agreement-info' => 'Naskah Perjanjian',
        'tab-agreement-assessment' => 'Kajian Perjanjian',
        'show-proposal-button' => 'Tampilkan Usulan',
        'hide-proposal-button' => 'Sembunyikan Usulan',
        'no-agreement-data' => 'Belum ada data perjanjian.',
        'no-agreement-assessment-data' => 'Belum ada data kajian perjanjian.',
        'grant-overview' => 'Informasi Umum',
        'label-activity-name' => 'Nama Kegiatan',
        'label-satker' => 'Satuan Kerja',
        'label-polda' => 'Satuan Induk',
        'label-type' => 'Jenis Hibah',
        'label-value' => 'Nilai Hibah',
        'label-status' => 'Status',
        'label-planning-number' => 'Nomor Perencanaan',
        'label-agreement-number' => 'Nomor Perjanjian',
        'donor-info' => 'Informasi Pemberi Hibah',
        'label-donor-name' => 'Nama Pemberi',
        'label-donor-origin' => 'Asal Pemberi Hibah',
        'label-donor-category' => 'Kategori',
        'label-donor-country' => 'Negara',
        'label-donor-address' => 'Alamat',
        'status-timeline' => 'Riwayat Status',
        'no-status-history' => 'Belum ada riwayat status.',
        'section-budget' => 'Rencana Anggaran Biaya',
        'section-schedule' => 'Timeline Kegiatan',
        'column-budget-description' => 'Uraian',
        'column-budget-value' => 'Nilai',
        'column-schedule-activity' => 'Uraian Kegiatan',
        'column-schedule-start' => 'Tanggal Mulai',
        'column-schedule-end' => 'Tanggal Selesai',
        'no-proposal-data' => 'Belum ada data usulan.',
        'no-budget-data' => 'Belum ada data anggaran.',
        'section-withdrawal' => 'Rencana Penarikan Hibah',
        'column-withdrawal-description' => 'Uraian',
        'column-withdrawal-date' => 'Tanggal Penarikan',
        'column-withdrawal-value' => 'Nilai',
        'no-withdrawal-data' => 'Belum ada data rencana penarikan.',
        'no-schedule-data' => 'Belum ada data jadwal.',
        'no-assessment-data' => 'Belum ada data pengkajian.',
        'satker-assessment' => 'Kajian Satuan Kerja',
        'polda-result' => 'Hasil Kajian Satuan Induk',
        'mabes-result' => 'Hasil Kajian Mabes',
        'edit-assessment' => 'Edit Pengkajian',
        'generate-document' => 'Cetak Dokumen',
        'tab-document-history' => 'Dokumen',
        'document-date-label' => 'Tanggal Dokumen',
        'generated-at' => 'Dicetak pada',
        'no-document-generated' => 'Belum pernah dicetak.',
    ],

    // Grant Agreement — Index
    'grant-agreement' => [
        'title' => 'Perjanjian Hibah',
        'create-button' => 'Buat Perjanjian',
        'column-name' => 'Nama Kegiatan',
        'column-donor' => 'Pemberi Hibah',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada perjanjian hibah.',
        'badge-filling-reception' => 'Dasar Penerimaan',
        'badge-filling-donor' => 'Data Pemberi Hibah',
        'badge-creating-assessment' => 'Dokumen Kajian',
        'badge-filling-harmonization' => 'Harmonisasi',
        'badge-filling-additional' => 'Materi Tambahan',
        'badge-filling-other' => 'Materi Lainnya',
        'badge-uploading-draft' => 'Draft Perjanjian',
        'badge-submitted' => 'Diajukan',
        'badge-revising' => 'Revisi',
        'badge-revision-resubmitted' => 'Revisi Diajukan',
        'submit-button' => 'Ajukan ke Polda',
        'submit-incomplete' => 'Lengkapi semua langkah sebelum mengajukan.',
        'submit-confirm-title' => 'Ajukan Perjanjian Hibah',
        'submit-confirm-description' => 'Yakin ingin mengajukan perjanjian hibah ini ke Polda?',
    ],

    // Grant Agreement — Step 1: Dasar Penerimaan
    'grant-agreement-reception' => [
        'title' => 'Dasar Penerimaan Hibah',
        'label-activity-name' => 'Nama kegiatan yang akan dibiayai oleh hibah',
        'label-letter-number' => 'Nomor Surat',
        'label-donor-letter' => 'Surat dari Pemberi Hibah',
        'donor-letter-hint' => 'PDF, JPG, atau PNG. Maksimal 10MB.',
        'label-objective-detail' => 'Detail tujuan',
        'linked-to-planning' => 'Nomor surat cocok dengan nomor usulan. Data usulan akan dimuat.',
    ],

    // Grant Agreement — Step 2: Pemberi Hibah
    'grant-agreement-donor' => [
        'title' => 'Pemberi Hibah',
        'readonly-heading' => 'Data Pemberi Hibah dari Usulan',
        'readonly-description' => 'Data pemberi hibah telah dimuat dari proses usulan dan tidak dapat diubah.',
    ],

    // Grant Agreement — Step 3: Kajian
    'grant-agreement-assessment' => [
        'title' => 'Dokumen Kajian Hibah',
    ],

    // Grant Agreement — Step 4: Harmonisasi Naskah
    'grant-agreement-harmonization' => [
        'title' => 'Harmonisasi Naskah Perjanjian Hibah',
        'label-grant-forms' => 'Bentuk Hibah',
        'section-budget' => 'Rencana Anggaran Kebutuhan',
        'section-withdrawal' => 'Rencana Penarikan Hibah',
        'label-withdrawal-date' => 'Tanggal Penarikan',
        'add-withdrawal' => 'Tambah Rencana Penarikan',
        'section-supervision' => 'Mekanisme Pengawasan Hibah',
        'label-supervision' => 'Mekanisme Pengawasan',
        'error-withdrawal-exceeds-budget' => 'Total rencana penarikan tidak boleh melebihi total anggaran kebutuhan.',
    ],

    // Grant Agreement — Step 5: Materi Tambahan Kesiapan
    'grant-agreement-additional' => [
        'title' => 'Materi Tambahan Kesiapan Hibah',
    ],

    // Grant Agreement — Step 6: Materi Tambahan Lainnya
    'grant-agreement-other' => [
        'title' => 'Materi Kesiapan Hibah Lainnya',
        'skip' => 'Lewati',
    ],

    // Grant Agreement — Step 7: Draft Naskah Perjanjian
    'grant-agreement-draft' => [
        'title' => 'Draft Naskah Perjanjian Hibah',
        'label-file' => 'File Draft Naskah Perjanjian',
        'hint-file' => 'Unggah file PDF, maksimal 20MB.',
    ],

    // Tag Management
    'tag-management' => [
        'title' => 'Manajemen Kategori',
        'label-name' => 'Nama Kategori',
        'placeholder-name' => 'Masukkan nama kategori...',
        'create-button' => 'Tambah Kategori',
        'column-name' => 'Nama',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada kategori.',
        'edit-modal-title' => 'Edit Kategori',
    ],

    // Grant Document Generation
    'grant-document' => [
        'label-date' => 'Tanggal Dokumen',
        'show-preview' => 'Lihat Pratinjau',
        'hide-preview' => 'Sembunyikan Pratinjau',
        'download-pdf' => 'Unduh PDF',
        'no-chief-title' => 'Kepala Satker Tidak Tersedia',
        'no-chief-description' => 'Tidak ada kepala satker yang sedang menjabat. Silakan tambahkan kepala satker terlebih dahulu untuk dapat mencetak dokumen.',
    ],

    // Donor Listing
    'donor-listing' => [
        'title' => 'Daftar Pemberi Hibah',
        'search-placeholder' => 'Cari nama pemberi hibah...',
        'column-name' => 'Nama',
        'column-origin' => 'Asal',
        'column-category' => 'Kategori',
        'column-country' => 'Negara',
        'column-grant-count' => 'Jumlah Hibah',
        'column-action' => 'Aksi',
        'empty-state' => 'Belum ada pemberi hibah.',
    ],

    // Donor Detail
    'donor-detail' => [
        'title' => 'Detail Pemberi Hibah',
        'section-donor-info' => 'Informasi Pemberi Hibah',
        'label-name' => 'Nama',
        'label-origin' => 'Asal',
        'label-address' => 'Alamat',
        'label-country' => 'Negara',
        'label-province' => 'Provinsi',
        'label-regency' => 'Kabupaten/Kota',
        'label-phone' => 'Nomor Telp/Faks',
        'label-email' => 'Email',
        'label-category' => 'Kategori',
        'section-grants' => 'Daftar Hibah',
        'column-grant-name' => 'Nama Kegiatan',
        'column-satker' => 'Satuan Kerja',
        'column-status' => 'Status',
        'column-action' => 'Aksi',
        'empty-grants' => 'Belum ada hibah terkait.',
        'section-tags' => 'Kategori',
        'empty-tags' => 'Belum ada kategori.',
    ],

];
