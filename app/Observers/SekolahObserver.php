<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Auth;

class SekolahObserver
{
    public function updated(Sekolah $sekolah): void
    {
        $changes = $sekolah->getChanges();

        // updated_at tidak perlu dicatat sebagai perubahan data
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $details = [];

        foreach ($changes as $field => $newValue) {
            $details[$field] = [
                'lama' => $sekolah->getOriginal($field),
                'baru' => $newValue,
            ];
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'sekolah_id' => $sekolah->id,
            'action' => 'updated',
            'changes' => $details,
        ]);
    }
}
