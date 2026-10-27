<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;

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

        $features = Sekolah::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when(
                $request->filled('jenjang'),
                fn ($q) => $q->where('jenjang', $request->jenjang)
            )
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where('status', $request->status)
            )
            ->get()
            ->map(fn ($s) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',

                    // GeoJSON menggunakan urutan [longitude, latitude].
                    'coordinates' => [
                        (float) $s->longitude,
                        (float) $s->latitude,
                    ],
                ],
                'properties' => [
                    'npsn' => $s->npsn,
                    'nama' => $s->nama,
                    'jenjang' => $s->jenjang,
                    'status' => $s->status,
                    'alamat' => $s->alamat,
                    'kecamatan' => $s->kecamatan,
                    'telepon' => $s->telepon,
                ],
            ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
