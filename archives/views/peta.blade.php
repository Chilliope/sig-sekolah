<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Peta Sekolah</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            font-family: system-ui, sans-serif;
        }

        header {
            min-height: 56px;
            box-sizing: border-box;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: #1e3a8a;
            color: #fff;
        }

        header h1 {
            font-size: 18px;
            margin: 0;
        }

        header select,
        header input {
            padding: 7px 10px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: white;
            font-size: 13px;
        }

        header input {
            width: 200px;
        }

        header button {
            padding: 7px 12px;
            border: none;
            border-radius: 6px;
            background: #2563eb;
            color: white;
            cursor: pointer;
        }

        header button:hover {
            background: #1d4ed8;
        }

        header a {
            color: #bfdbfe;
            margin-left: auto;
        }

        #jumlah {
            white-space: nowrap;
            font-size: 14px;
        }

        #map {
            height: calc(100% - 56px);
            width: 100%;
        }

        .legend {
            background: white;
            padding: 10px 12px;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
            line-height: 1.8;
        }

        .legend h4 {
            margin: 0 0 5px;
            font-size: 14px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            display: inline-block;
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 600px) {
            header {
                min-height: 56px;
                gap: 10px;
            }

            header h1 {
                width: 100%;
            }

            header a {
                margin-left: 0;
            }

            #map {
                height: calc(100% - 150px);
                min-height: 400px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>Peta Sekolah</h1>

        <select id="filter-jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>

        <select id="filter-status">
            <option value="">Semua status</option>
            <option value="Negeri">Negeri</option>
            <option value="Swasta">Swasta</option>
        </select>

        <input type="search" id="cari-sekolah" placeholder="Cari nama sekolah..." aria-label="Cari nama sekolah"
            autocomplete="off">

        <button type="button" id="tombol-cari">
            Cari
        </button>

        <span id="jumlah">Memuat data...</span>

        @auth
            <a href="{{ route('dashboard') }}">Dashboard</a>
        @else
            <a href="{{ route('login') }}">Masuk</a>
        @endauth
    </header>

    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
        const map = L.map('map').setView([-3.3194, 114.5908], 12);

        const osm = L.tileLayer(
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }
        );

        const satelit = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri'
            }
        );

        const topografi = L.tileLayer(
            'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 17,
                attribution: 'Map data: &copy; OpenStreetMap contributors, SRTM | Map style: &copy; OpenTopoMap'
            }
        );

        // Peta dasar pertama.
        osm.addTo(map);

        // Pilihan peta dasar.
        const baseMaps = {
            'OpenStreetMap': osm,
            'Satelit': satelit,
            'Topografi': topografi
        };

        L.control.layers(baseMaps, null, {
            collapsed: true,
            position: 'topright'
        }).addTo(map);

        const warnaJenjang = {
            SD: '#16a34a',
            SMP: '#2563eb',
            SMA: '#dc2626',
            SMK: '#ea580c'
        };

        function buatMarker(feature, latlng) {
            const jenjang = feature.properties.jenjang;
            const warna = warnaJenjang[jenjang] || '#6b7280';

            return L.marker(latlng, {
                icon: L.divIcon({
                    className: '',
                    html: `
                        <span style="
                            display: block;
                            width: 16px;
                            height: 16px;
                            background: ${warna};
                            border: 2px solid white;
                            border-radius: 50%;
                            box-shadow: 0 1px 4px rgba(0,0,0,0.4);
                        "></span>
                    `,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                })
            });
        }

        function esc(teks) {
            const d = document.createElement('div');
            d.textContent = teks ?? '';
            return d.innerHTML;
        }

        const cluster = L.markerClusterGroup({
            showCoverageOnHover: false,
            spiderfyOnMaxZoom: true,
            maxClusterRadius: 60
        });

        map.addLayer(cluster);

        const lapisan = L.geoJSON(null, {

            pointToLayer: buatMarker,

            onEachFeature: (fitur, layer) => {
                const p = fitur.properties;

                const koordinat = fitur.geometry.coordinates;
                const lng = koordinat[0];
                const lat = koordinat[1];

                const warna = warnaJenjang[p.jenjang] || '#6b7280';

                const popup = `
                        ${
        p.logo_url
            ? `<img src="${esc(p.logo_url)}"
                            alt="Logo sekolah"
                            style="width:80px;height:80px;object-fit:contain;margin-bottom:8px;">`
            : ''
    }

                    <strong>${esc(p.nama)}</strong><br>
                    NPSN: ${esc(p.npsn)}<br>

                    <span style="color: ${warna}; font-weight: bold;">
                        ${esc(p.jenjang)}
                    </span>
                    - ${esc(p.status)}<br>

                    ${esc(p.alamat)}, ${esc(p.kecamatan)}<br>
                    Telp: ${esc(p.telepon) || '-'}<br>

                    Kepala sekolah: ${esc(p.kepala_sekolah) || '-'}<br>
                    Akreditasi: ${esc(p.akreditasi) || '-'}<br>

                    Siswa: ${p.jumlah_siswa ?? '-'}
                    | Guru: ${p.jumlah_guru ?? '-'}<br>

                    <small>Koordinat: ${lat}, ${lng}</small>

                    <a href="${esc(p.profile_url)}"
   style="
       display:inline-block;
       margin-top:8px;
       padding:7px 12px;
       background:#1d4ed8;
       color:white;
       border-radius:6px;
       text-decoration:none;
   ">
   Lihat Profil Sekolah
</a>
                `;

                layer.bindPopup(popup);
            }
        });

        const legend = L.control({
            position: 'bottomright'
        });

        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'legend');

            div.innerHTML = `
                <h4>Jenjang Sekolah</h4>

                <div class="legend-item">
                    <span class="legend-color" style="background:#16a34a"></span>
                    SD
                </div>

                <div class="legend-item">
                    <span class="legend-color" style="background:#2563eb"></span>
                    SMP
                </div>

                <div class="legend-item">
                    <span class="legend-color" style="background:#dc2626"></span>
                    SMA
                </div>

                <div class="legend-item">
                    <span class="legend-color" style="background:#ea580c"></span>
                    SMK
                </div>

                <div class="legend-item">
                    <span class="legend-color" style="background:#6b7280"></span>
                    Lainnya
                </div>
            `;

            return div;
        };

        legend.addTo(map);

        async function muatData(jenjang = '', status = '') {
            const jumlah = document.getElementById('jumlah');

            try {
                jumlah.textContent = 'Memuat data...';

                const url = new URL(
                    @json(route('peta.data')),
                    window.location.origin
                );

                if (jenjang) {
                    url.searchParams.set('jenjang', jenjang);
                }

                if (status) {
                    url.searchParams.set('status', status);
                }

                const respon = await fetch(url);

                if (!respon.ok) {
                    throw new Error(
                        'Gagal mengambil data peta: ' + respon.status
                    );
                }

                const data = await respon.json();

                if (!data || !Array.isArray(data.features)) {
                    throw new Error('Format data GeoJSON tidak sesuai.');
                }

                // Bersihkan marker sebelumnya.
                cluster.clearLayers();
                lapisan.clearLayers();

                // Masukkan data baru.
                lapisan.addData(data);

                // Masukkan setiap marker ke cluster.
                lapisan.eachLayer(function(marker) {
                    cluster.addLayer(marker);
                });

                // Tampilkan jumlah sekolah.
                jumlah.textContent = data.features.length + ' sekolah';

                // Atur posisi peta.
                if (data.features.length > 0) {
                    map.fitBounds(lapisan.getBounds().pad(0.1));
                } else {
                    map.setView([-3.3194, 114.5908], 12);
                }

            } catch (error) {
                console.error('Kesalahan memuat peta:', error);
                jumlah.textContent = 'Data gagal dimuat';
            }
        }

        function cariSekolah() {
            const input = document
                .getElementById('cari-sekolah')
                .value
                .trim()
                .toLowerCase();

            if (!input) {
                alert('Masukkan nama sekolah yang ingin dicari.');
                return;
            }

            let ditemukan = null;

            lapisan.eachLayer(function(layer) {
                const nama = String(
                    layer.feature?.properties?.nama ?? ''
                ).trim().toLowerCase();

                if (nama.includes(input) && !ditemukan) {
                    ditemukan = layer;
                }
            });

            if (!ditemukan) {
                alert(
                    'Sekolah tidak ditemukan. Periksa filter jenjang dan status, lalu coba lagi.'
                );
                return;
            }

            const posisi = ditemukan.getLatLng();

            map.flyTo(posisi, 17, {
                animate: true,
                duration: 0.8
            });

            ditemukan.openPopup();
        }

        // Tombol Cari.
        document
            .getElementById('tombol-cari')
            .addEventListener('click', cariSekolah);

        // Pencarian menggunakan Enter.
        document
            .getElementById('cari-sekolah')
            .addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    cariSekolah();
                }
            });

        function terapkanFilter() {
            const jenjang = document
                .getElementById('filter-jenjang')
                .value;

            const status = document
                .getElementById('filter-status')
                .value;

            muatData(jenjang, status);
        }

        document
            .getElementById('filter-jenjang')
            .addEventListener('change', terapkanFilter);

        document
            .getElementById('filter-status')
            .addEventListener('change', terapkanFilter);

        muatData();
    </script>

</body>

</html>
