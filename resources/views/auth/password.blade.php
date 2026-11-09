
@extends('layouts.app')

@section('title', 'Ubah Kata Sandi')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="bg-white rounded-lg shadow p-6">

        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Ubah Kata Sandi
        </h1>

        <p class="text-gray-600 mb-6">
            Perbarui kata sandi akun Anda untuk menjaga keamanan akun.
        </p>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}"
              class="space-y-4">

            @csrf
            @method('PUT')

            <div>
                <label for="current_password"
                       class="block font-medium mb-1">
                    Kata Sandi Lama
                </label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                    autocomplete="current-password"
                    class="w-full border rounded px-3 py-2">

                @error('current_password')
                    <p class="text-red-600 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="password"
                       class="block font-medium mb-1">
                    Kata Sandi Baru
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full border rounded px-3 py-2">

                @error('password')
                    <p class="text-red-600 text-sm mt-1">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation"
                       class="block font-medium mb-1">
                    Konfirmasi Kata Sandi Baru
                </label>

                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex gap-3 pt-2">

                <button
                    type="submit"
                    class="px-5 py-2 bg-blue-700 text-white rounded hover:bg-blue-800">
                    Simpan Kata Sandi
                </button>

                <a
                    href="{{ route('dashboard') }}"
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Kembali
                </a>

            </div>

        </form>
    </div>
</div>
@endsection
