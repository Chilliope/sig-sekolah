<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetaController extends Controller
{
    public function index()
    {
        return view('peta');
    }

    public function geojson(Request $request)
    {
        $request->validate([
            'jenjang' => 'nullable|in:SD,SMP,SMA,SMK',
            'status' => 'nullable|in:Negeri,Swasta',
        ]);

        $features = Sekolah::with('profil')
            ->when($request->jenjang, function ($query, $jenjang) {
                $query->where('jenjang', $jenjang);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($s) {
                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            (float) $s->longitude,
                            (float) $s->latitude,
                        ],
                    ],
                    'properties' => [
                        'id' => $s->id,
                        'profile_url' => route('peta.profil', $s->id),
                        'npsn' => $s->npsn,
                        'nama' => $s->nama,
                        'jenjang' => $s->jenjang,
                        'status' => $s->status,
                        'alamat' => $s->alamat,
                        'kecamatan' => $s->kecamatan,
                        'telepon' => $s->telepon,
                        'kepala_sekolah' => $s->profil?->kepala_sekolah,
                        'akreditasi' => $s->profil?->akreditasi,
                        'jumlah_siswa' => $s->profil?->jumlah_siswa,
                        'jumlah_guru' => $s->profil?->jumlah_guru,
                        'logo_url' => $s->profil?->logo_path
                            ? Storage::disk('public')->url($s->profil->logo_path)
                            : null,
                    ],
                ];
            });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function profil(Sekolah $sekolah)
    {
        $sekolah->load('profil');

        return view('peta.profil', compact('sekolah'));
    }
}
