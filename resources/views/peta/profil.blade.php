@extends('layouts.app')

@section('title', 'Profil Sekolah')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <a href="{{ route('peta.index') }}"
       class="inline-flex items-center text-blue-700 hover:underline">
        &larr; Kembali ke Peta
    </a>

    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex flex-col sm:flex-row gap-5 items-start">

            @if ($sekolah->profil?->logo_path)
                <img
                    src="{{ asset('storage/' . $sekolah->profil->logo_path) }}"
                    alt="Logo {{ $sekolah->nama }}"
                    class="w-28 h-28 object-contain border rounded-lg p-2"
                >
            @endif

            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $sekolah->nama }}
                </h1>

                <p class="text-gray-600 mt-1">
                    {{ $sekolah->jenjang }} - {{ $sekolah->status }}
                </p>

                <p class="text-sm text-gray-500 mt-2">
                    NPSN: {{ $sekolah->npsn }}
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Sekolah</h2>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Alamat</p>
                <p class="font-medium">{{ $sekolah->alamat }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Kecamatan</p>
                <p class="font-medium">{{ $sekolah->kecamatan }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Telepon</p>
                <p class="font-medium">{{ $sekolah->telepon ?: '-' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Kepala Sekolah</p>
                <p class="font-medium">
                    {{ $sekolah->profil?->kepala_sekolah ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Akreditasi</p>
                <p class="font-medium">
                    {{ $sekolah->profil?->akreditasi ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Tahun Berdiri</p>
                <p class="font-medium">
                    {{ $sekolah->profil?->tahun_berdiri ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Jumlah Siswa</p>
                <p class="font-medium">
                    {{ $sekolah->profil?->jumlah_siswa ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Jumlah Guru</p>
                <p class="font-medium">
                    {{ $sekolah->profil?->jumlah_guru ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">
                    {{ $sekolah->profil?->email ?: '-' }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Website</p>

                @if ($sekolah->profil?->website)
                    <a href="{{ $sekolah->profil->website }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="text-blue-700 hover:underline">
                        {{ $sekolah->profil->website }}
                    </a>
                @else
                    <p class="font-medium">-</p>
                @endif
            </div>
        </div>

        <div class="mt-5">
            <p class="text-sm text-gray-500 mb-1">Deskripsi</p>
            <p class="text-gray-800 whitespace-pre-line">
                {{ $sekolah->profil?->deskripsi ?: 'Belum ada deskripsi sekolah.' }}
            </p>
        </div>
    </div>

</div>
@endsection
