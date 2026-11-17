@extends('layouts.public')

@section('title', 'Cek Lokasi')

@section('content')
    <div class="toolbar">
        Klik di mana saja pada peta untuk melihat alamat dan sekolah terdekat.
    </div>

    <div id="peta" class="peta"></div>

    <div id="hasil" class="panel"
        style="
        top: 64px;
        right: 12px;
        width: 320px;
        max-height: 70%;
        overflow: auto;
        display: none;
    ">
    </div>
@endsection

@push('scripts')
    <script>
        const map = Peta.buat('peta');
        const lapisan = L.layerGroup().addTo(map);
        const panel = document.getElementById('hasil');

        let semua = [];
        let terakhir = 0;

        // Mengambil data sekolah
        Peta.ambilSekolah().then((fitur) => {
            semua = fitur;
        });

        // Mencari sekolah terdekat dari titik [lat, lng]
        function terdekat(titik, n = 3) {
            return semua
                .map((f) => ({
                    fitur: f,
                    jarak: Peta.jarakKm(titik, Peta.latlng(f))
                }))
                .sort((a, b) => a.jarak - b.jarak)
                .slice(0, n);
        }

        // Mengubah koordinat menjadi alamat
        async function ambilAlamat(lat, lng) {
            const url =
                'https://nominatim.openstreetmap.org/reverse' +
                '?format=jsonv2&addressdetails=1&accept-language=id' +
                `&lat=${lat}&lon=${lng}`;

            const respon = await fetch(url);

            if (!respon.ok) {
                throw new Error('HTTP ' + respon.status);
            }

            return respon.json();
        }


        map.on('click', async (e) => {
            // Membatasi permintaan Nominatim maksimal 1 kali per detik
            const sekarang = Date.now();

            if (sekarang - terakhir < 1200) {
                return;
            }

            terakhir = sekarang;

            const {
                lat,
                lng
            } = e.latlng;
            const titik = [lat, lng];

            // Menghapus marker dan garis sebelumnya
            lapisan.clearLayers();

            // Menampilkan marker lokasi yang diklik
            L.circleMarker(titik, {
                radius: 9,
                color: '#fff',
                weight: 3,
                fillColor: '#ef4444',
                fillOpacity: 1,
            }).addTo(lapisan);

            // Mencari tiga sekolah terdekat
            const dekat = terdekat(titik);

            // Menggambar garis menuju sekolah terdekat
            dekat.forEach((d) => {
                L.polyline([titik, Peta.latlng(d.fitur)], {
                    color: '#2563eb',
                    weight: 2,
                    dashArray: '6 6',
                }).addTo(lapisan);
            });

            // Menampilkan status pencarian
            panel.style.display = 'block';
            panel.textContent = 'Mencari alamat...';

            let bagianAlamat = '<em>Alamat tidak dapat dimuat.</em>';

            try {
                const data = await ambilAlamat(lat, lng);
                const a = data.address || {};

                const baris = [
                    ['Jalan', a.road],
                    [
                        'Kelurahan/Desa',
                        a.suburb || a.neighbourhood || a.village
                    ],
                    [
                        'Kota/Kabupaten',
                        a.city || a.town || a.municipality || a.county
                    ],
                    ['Provinsi', a.state],
                    ['Kode pos', a.postcode],
                ].map(([k, v]) => `
            <tr>
                <td style="padding: 2px 8px 2px 0; color: #6b7280;">
                    ${k}
                </td>
                <td>${Peta.esc(v) || '-'}</td>
            </tr>
        `).join('');

                bagianAlamat = `
            <p style="margin: 6px 0;">
                ${Peta.esc(data.display_name || '-')}
            </p>

            <table style="font-size: 13px;">
                ${baris}
            </table>
        `;

            } catch (err) {
                console.error(err);
            }

            // Membuat daftar sekolah terdekat
            const daftar = dekat.map((d) => `
        <li
            data-npsn="${Peta.esc(d.fitur.properties.npsn)}"
            style="
                padding: 6px 0;
                border-top: 1px solid #e5e7eb;
                cursor: pointer;
            "
        >
            <strong>
                ${Peta.esc(d.fitur.properties.nama)}
            </strong>
            <br>

            <small>
                ${d.jarak.toFixed(2)} km (garis lurus)
            </small>
        </li>
    `).join('');

            // Menampilkan seluruh hasil
            panel.innerHTML = `
        <strong>Koordinat</strong>

        <div style="font-family: monospace;">
            ${lat.toFixed(6)}, ${lng.toFixed(6)}
        </div>

        <strong style="display: block; margin-top: 8px;">
            Alamat
        </strong>

        ${bagianAlamat}

        <strong style="display: block; margin-top: 8px;">
            Sekolah terdekat
        </strong>

        <ul style="
            list-style: none;
            margin: 4px 0 0;
            padding: 0;
        ">
            ${daftar}
        </ul>
    `;
        });


        // Klik sekolah pada daftar untuk menuju lokasinya
        panel.addEventListener('click', (e) => {
            const item = e.target.closest('li[data-npsn]');

            if (!item) {
                return;
            }

            const fitur = semua.find(
                (f) => String(f.properties.npsn) === item.dataset.npsn
            );

            if (!fitur) {
                return;
            }

            const titikSekolah = Peta.latlng(fitur);

            map.flyTo(titikSekolah, 17);

            L.popup()
                .setLatLng(titikSekolah)
                .setContent(`
            <strong>${Peta.esc(fitur.properties.nama)}</strong><br>
            ${Peta.esc(fitur.properties.alamat)}
        `)
                .openOn(map);
        });
    </script>
@endpush
