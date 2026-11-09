<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserStatusController extends Controller
{
    public function toggle(Request $request, User $user)
    {
        // Pastikan hanya admin yang dapat mengubah status akun
        abort_unless($request->user()->role === 'admin', 403);

        // Admin tidak boleh menonaktifkan akunnya sendiri
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with(
            'success',
            "Akun {$user->name} berhasil {$status}."
        );
    }
}
