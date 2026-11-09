<?php

namespace App\Http\Controllers;

use App\Models\ProfilSekolah;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfilSekolahController extends Controller
{
    public function edit(Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);
        $profil = $sekolah->profil ?? new ProfilSekolah();
        return view('admin.sekolah.profil', compact('sekolah', 'profil'));
    }
    public function update(Request $request, Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        $data = $request->validate([
            'kepala_sekolah' => ['nullable', 'string', 'max:150'],
            'email'          => ['nullable', 'email', 'max:150'],
            'website'        => ['nullable', 'url', 'max:255'],
            'tahun_berdiri'  => ['nullable', 'integer', 'between:1800,' . date('Y')],
            'akreditasi'     => ['nullable', Rule::in(
                ['A', 'B', 'C', 'Belum Terakreditasi']
            )],
            'jumlah_siswa'   => ['nullable', 'integer', 'min:0'],
            'jumlah_guru'    => ['nullable', 'integer', 'min:0'],
            'deskripsi'      => ['nullable', 'string', 'max:2000'],

            // Validasi logo
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $profilLama = $sekolah->profil;
        $logoLama = $profilLama?->logo_path;

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')
                ->store('logo-sekolah', 'public');
        }

        $sekolah->profil()->updateOrCreate([], $data);

        // Hapus logo lama setelah logo baru berhasil disimpan
        if (isset($data['logo_path']) && $logoLama) {
            Storage::disk('public')->delete($logoLama);
        }

        return back()->with('success', 'Profil sekolah berhasil disimpan.');
    }
}
