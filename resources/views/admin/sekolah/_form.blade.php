@php
    $teks = [
        'npsn' => 'NPSN (8 digit)',
        'nama' => 'Nama Sekolah',
        'kecamatan' => 'Kecamatan',
        'telepon' => 'Telepon',
    ];

    $pilihan = [
        'jenjang' => ['SD', 'SMP', 'SMA', 'SMK'],
        'status' => ['Negeri', 'Swasta'],
    ];

    $cls = 'w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 rounded-xl bg-white p-6 shadow">

    {{-- Kolom informasi sekolah --}}
    <div class="space-y-4">

        @foreach ($teks as $name => $label)
            <div>
                <label for="{{ $name }}" class="mb-1 block font-medium text-gray-700">
                    {{ $label }}
                </label>

                <input type="text" id="{{ $name }}" name="{{ $name }}"
                    value="{{ old($name, $sekolah->$name) }}" class="{{ $cls }}"
                    @if ($name === 'npsn') maxlength="8"
                        inputmode="numeric" @endif
                    @if (in_array($name, ['npsn', 'nama', 'kecamatan'], true)) required @endif>

                @error($name)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        @foreach ($pilihan as $name => $opsi)
            <div>
                <label for="{{ $name }}" class="mb-1 block font-medium text-gray-700">
                    {{ ucfirst($name) }}
                </label>

                <select id="{{ $name }}" name="{{ $name }}" class="{{ $cls }}" required>
                    <option value="">-- Pilih {{ ucfirst($name) }} --</option>

                    @foreach ($opsi as $o)
                        <option value="{{ $o }}" @selected(old($name, $sekolah->$name) === $o)>
                            {{ $o }}
                        </option>
                    @endforeach
                </select>

                @error($name)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div>
            <label for="alamat" class="mb-1 block font-medium text-gray-700">
                Alamat
            </label>

            <textarea id="alamat" name="alamat" rows="3" class="{{ $cls }}" required>{{ old('alamat', $sekolah->alamat) }}</textarea>

            @error('alamat')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Kolom peta dan koordinat --}}
    <div class="space-y-4">

        <div>
            <p class="font-medium text-gray-700">Lokasi Sekolah</p>

            <p class="mt-1 text-sm text-gray-500">
                Klik peta atau geser marker untuk menentukan lokasi sekolah.
            </p>
        </div>

        <div id="map-picker" style="height: 400px; width: 100%;" class="rounded-lg border border-gray-300"></div>

        <button type="button" id="gunakan-lokasi"
            class="mt-3 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            <span aria-hidden="true">📍</span>
            Gunakan lokasi saya
        </button>

        <p id="status-lokasi" class="mt-2 text-sm text-gray-500"></p>


        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            <div>
                <label for="latitude" class="mb-1 block font-medium text-gray-700">
                    Latitude
                </label>

                <input type="number" step="any" min="-90" max="90" id="latitude" name="latitude"
                    value="{{ old('latitude', $sekolah->latitude) }}" class="{{ $cls }}" required>

                @error('latitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="longitude" class="mb-1 block font-medium text-gray-700">
                    Longitude
                </label>

                <input type="number" step="any" min="-180" max="180" id="longitude" name="longitude"
                    value="{{ old('longitude', $sekolah->longitude) }}" class="{{ $cls }}" required>

                @error('longitude')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="flex flex-wrap gap-3 pt-2">

            <button type="submit"
                class="rounded-lg bg-blue-700 px-5 py-2.5 font-medium text-white transition hover:bg-blue-800">
                Simpan
            </button>

            <a href="{{ route('admin.sekolah.index') }}"
                class="rounded-lg bg-gray-200 px-5 py-2.5 font-medium text-gray-700 transition hover:bg-gray-300">
                Batal
            </a>

        </div>

    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const mapElement = document.getElementById('map-picker');

            if (!latInput || !lngInput || !mapElement || typeof L === 'undefined') {
                console.error('Elemen peta atau library Leaflet tidak ditemukan.');
                return;
            }

            const latAwal = parseFloat(latInput.value);
            const lngAwal = parseFloat(lngInput.value);

            const koordinatValid =
                latInput.value.trim() !== '' &&
                lngInput.value.trim() !== '' &&
                Number.isFinite(latAwal) &&
                Number.isFinite(lngAwal) &&
                latAwal >= -90 && latAwal <= 90 &&
                lngAwal >= -180 && lngAwal <= 180;

            // Pusat Kota Banjarmasin jika koordinat belum tersedia.
            const pusat = koordinatValid ? [latAwal, lngAwal] : [-3.3194, 114.5908];

            const map = L.map(mapElement).setView(
                pusat,
                koordinatValid ? 16 : 13
            );

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const marker = L.marker(pusat, {
                draggable: true
            }).addTo(map);

            // Memperbarui input koordinat.
            function isiKoordinat(lat, lng) {
                latInput.value = lat.toFixed(7);
                lngInput.value = lng.toFixed(7);
            }

            const tombolLokasi = document.getElementById('gunakan-lokasi');
            const statusLokasi = document.getElementById('status-lokasi');

            tombolLokasi.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    statusLokasi.textContent =
                        'Browser kamu tidak mendukung fitur lokasi.';
                    return;
                }

                tombolLokasi.disabled = true;
                tombolLokasi.textContent = 'Mengambil lokasi...';
                statusLokasi.textContent =
                    'Izinkan akses lokasi pada browser jika diminta.';

                navigator.geolocation.getCurrentPosition(
                    function(posisi) {
                        const lat = posisi.coords.latitude;
                        const lng = posisi.coords.longitude;

                        // Mengisi koordinat pada form.
                        isiKoordinat(lat, lng);

                        // Memindahkan marker dan tampilan peta.
                        marker.setLatLng([lat, lng]);
                        map.setView([lat, lng], 17);

                        statusLokasi.textContent =
                            'Lokasi berhasil ditemukan. Periksa kembali posisi marker.';

                        tombolLokasi.disabled = false;
                        tombolLokasi.textContent = 'Gunakan lokasi saya';
                    },
                    function(error) {
                        let pesan = 'Gagal mendapatkan lokasi.';

                        if (error.code === 1) {
                            pesan = 'Izin lokasi ditolak. Izinkan akses lokasi di browser.';
                        } else if (error.code === 2) {
                            pesan = 'Lokasi tidak tersedia. Periksa GPS atau koneksi perangkat.';
                        } else if (error.code === 3) {
                            pesan = 'Waktu mencari lokasi habis. Silakan coba lagi.';
                        }

                        statusLokasi.textContent = pesan;
                        tombolLokasi.disabled = false;
                        tombolLokasi.textContent = 'Gunakan lokasi saya';
                    }, {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 0
                    }
                );
            });


            // Memilih lokasi dengan mengeklik peta.
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                isiKoordinat(e.latlng.lat, e.latlng.lng);
            });

            // Memperbarui koordinat ketika marker digeser.
            marker.on('dragend', function() {
                const posisi = marker.getLatLng();
                isiKoordinat(posisi.lat, posisi.lng);
            });

            // Memindahkan marker ketika koordinat diubah manual.
            function perbaruiMarker() {
                const latText = latInput.value.trim();
                const lngText = lngInput.value.trim();

                if (latText === '' || lngText === '') {
                    return;
                }

                const lat = Number(latText);
                const lng = Number(lngText);

                const valid =
                    Number.isFinite(lat) &&
                    Number.isFinite(lng) &&
                    lat >= -90 && lat <= 90 &&
                    lng >= -180 && lng <= 180;

                if (!valid) {
                    return;
                }

                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
            }

            latInput.addEventListener('change', perbaruiMarker);
            lngInput.addEventListener('change', perbaruiMarker);

            // Menyesuaikan ukuran peta setelah halaman ditampilkan.
            requestAnimationFrame(function() {
                map.invalidateSize();
            });
        });
    </script>
@endpush
