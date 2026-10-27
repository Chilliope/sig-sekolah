<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SekolahController extends Controller
{
    public function index(Request $request)
    {
        $sekolahs = Sekolah::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama', 'like', "%{$q}%")
                        ->orWhere('npsn', 'like', "%{$q}%")
                        ->orWhere('kecamatan', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();
        return view('admin.sekolah.index', compact('sekolahs'));
    }

    public function create()
    {
        return view('admin.sekolah.create');
    }

    public function store(Request $request)
    {
        Sekolah::create($request->validate($this->rules()));
        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        return view('admin.sekolah.edit', compact('sekolah'));
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $sekolah->update($request->validate($this->rules($sekolah->id)));
        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();
        return redirect()->route('admin.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }

    private function rules(?int $id = null): array
    {
        return [
            'npsn' => [
                'required',
                'digits:8',
                Rule::unique('sekolahs', 'npsn')->ignore($id)
            ],
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', Rule::in(['SD', 'SMP', 'SMA', 'SMK'])],
            'status' => ['required', Rule::in(['Negeri', 'Swasta'])],
            'alamat' => ['required', 'string'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'telepon' => ['nullable', 'string', 'max:20'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function data(Request $request)
    {
        $request->validate([
            'jenjang' => 'nullable|in:SD,SMP,SMA,SMK',
            'status' => 'nullable|in:Negeri,Swasta',
        ]);
        
        $query = Sekolah::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('jenjang')) {
            $query->where('jenjang', $request->jenjang);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sekolah = $query->get();

        $features = $sekolah->map(function ($item) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $item->longitude,
                        (float) $item->latitude,
                    ],
                ],
                'properties' => [
                    'npsn' => $item->npsn,
                    'nama' => $item->nama,
                    'jenjang' => $item->jenjang,
                    'status' => $item->status,
                    'alamat' => $item->alamat,
                    'kecamatan' => $item->kecamatan,
                    'telepon' => $item->telepon,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
