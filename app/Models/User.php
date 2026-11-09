<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\UserRole;

#[Fillable(['name', 'email', 'password', 'role', 'sekolah_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
    public const ROLE_ADMIN = 'admin';
    public const ROLE_DINAS = 'dinas';
    public const ROLE_SEKOLAH = 'sekolah';

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isDinas(): bool
    {
        return $this->role === UserRole::DINAS;
    }

    public function isSekolah(): bool
    {
        return $this->role === UserRole::SEKOLAH;
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_DINAS => 'Dinas Pendidikan',
            default => 'Sekolah',
        };
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
        ];
    }
}
