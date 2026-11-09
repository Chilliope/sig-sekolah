<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case DINAS = 'dinas';
    case SEKOLAH = 'sekolah';
}
