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

];
