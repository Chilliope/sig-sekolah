@extends('layouts.public')

@section('title', 'Peta Sekolah')

@section('content')

    <div class="toolbar">
        <label>Jenjang
            <select id="filter-jenjang">
                <option value="">Semua jenjang</option>
                <option value="SD">SD</option>
                <option value="SMP">SMP</option>
                <option value="SMA">SMA</option>
                <option value="SMK">SMK</option>
            </select>
        </label>

        <span id="jumlah"></span>

        <button type="button" id="btn-unduh-geojson">
            Unduh GeoJSON
        </button>
    </div>

    <div id="peta" class="peta"></div>

@endsection

@push('scripts')
    <script>
        const map = Peta.buat('peta');
        const lapisan = L.layerGroup().addTo(map);

        document.getElementById('btn-unduh-geojson').addEventListener('click', unduhGeoJSON);

        async function muat(jenjang = '') {
            try {
                const fitur = await Peta.ambilSekolah(jenjang);

                lapisan.clearLayers();

                fitur.forEach((f) => {
                    L.marker(Peta.latlng(f))
                        .bindPopup(Peta.popup(f))
                        .addTo(lapisan);
                });

                document.getElementById('jumlah').textContent =
                    fitur.length + ' sekolah';

                if (fitur.length > 0) {
                    map.fitBounds(
                        L.latLngBounds(fitur.map(Peta.latlng)).pad(0.1)
                    );
                }

            } catch (e) {
                document.getElementById('jumlah').textContent =
                    'Gagal memuat data';
            }
        }

        async function unduhGeoJSON() {
            const tombol = document.getElementById('btn-unduh-geojson');
            const jenjang = document.getElementById('filter-jenjang').value;

            try {
                tombol.disabled = true;
                tombol.textContent = 'Mengunduh...';

                // Buat URL endpoint GeoJSON
                const url = new URL(window.PETA_URL.data);

                // Ikuti filter jenjang yang dipilih
                if (jenjang) {
                    url.searchParams.set('jenjang', jenjang);
                }

                // Ambil data GeoJSON dari Laravel
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error('Gagal mengambil data GeoJSON');
                }

                const geojson = await response.json();

                // Ubah data menjadi file JSON
                const blob = new Blob(
                    [JSON.stringify(geojson, null, 2)], {
                        type: 'application/geo+json'
                    }
                );

                // Buat link download sementara
                const downloadUrl = URL.createObjectURL(blob);
                const link = document.createElement('a');

                link.href = downloadUrl;
                link.download = jenjang ?
                    `data-sekolah-${jenjang.toLowerCase()}.geojson` :
                    'data-sekolah.geojson';

                document.body.appendChild(link);
                link.click();
                link.remove();

                URL.revokeObjectURL(downloadUrl);

            } catch (error) {
                console.error('Download GeoJSON error:', error);
                alert('Gagal mengunduh data GeoJSON.');
            } finally {
                tombol.disabled = false;
                tombol.textContent = 'Unduh GeoJSON';
            }
        }
        document.getElementById('filter-jenjang')
            .addEventListener('change', (e) => muat(e.target.value));

        muat();
    </script>
@endpush
