<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'sekolah_id',
        'action',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }
}
