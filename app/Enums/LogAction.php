<?php

namespace App\Enums;

enum LogAction: string
{
    case Create = 'CREATE';
    case Update = 'UPDATE';
    case Delete = 'DELETE';
    case Login = 'LOGIN';
    case Logout = 'LOGOUT';
    case Submit = 'SUBMIT';
    case Review = 'REVIEW';
    case Verify = 'VERIFY';
    case Reject = 'REJECT';
    case RequestRevision = 'REQUEST_REVISION';

    public function label(): string
    {
        return match ($this) {
            self::Create => 'Membuat',
            self::Update => 'Mengubah',
            self::Delete => 'Menghapus',
            self::Login => 'Masuk',
            self::Logout => 'Keluar',
            self::Submit => 'Mengajukan',
            self::Review => 'Mengkaji',
            self::Verify => 'Memverifikasi',
            self::Reject => 'Menolak',
            self::RequestRevision => 'Meminta Revisi',
        };
    }
}
