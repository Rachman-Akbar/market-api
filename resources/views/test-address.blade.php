<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Address Domain with Leaflet Maps</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 350px; width: 100%; border-radius: 0.5rem; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Simulasi Tambah Alamat (DDD Test)</h2>

        <form id="addressForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 mb-4">
    <label class="block text-sm font-bold text-amber-800 mb-1">Bearer Token (Untuk Auth API)</label>
    <input type="text" id="auth_token" placeholder="Masukkan token Anda di sini (tanpa kata 'Bearer')"
           class="mt-1 block w-full p-2 border border-amber-300 rounded-md shadow-sm bg-white text-sm font-mono">
    <p class="text-xs text-amber-600 mt-1">*Dapatkan token ini dari endpoint login Anda atau tinker.</p>
</div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Label Alamat</label>
                    <input type="text" id="label" value="Rumah Utama" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                    <input type="text" id="recipient_name" value="John Doe" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                <input type="text" id="phone_number" value="081234567890" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                <textarea id="full_address" rows="3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">Jl. Merdeka No. 123, RT 01/RW 02, Kecamatan Kebayoran Baru</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kota</label>
                    <input type="text" id="city" value="Jakarta Selatan" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Pos</label>
                    <input type="text" id="postal_code" value="12110" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            <div class="pt-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Lokasi (Geser Pin Peta)</label>
                <div id="map" class="border border-gray-300"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                <div>
                    <label class="block text-xs font-semibold text-gray-500">Latitude</label>
                    <input type="text" id="latitude" readonly class="mt-1 block w-full bg-gray-200 p-2 text-sm border border-gray-300 rounded-md text-gray-600">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500">Longitude</label>
                    <input type="text" id="longitude" readonly class="mt-1 block w-full bg-gray-200 p-2 text-sm border border-gray-300 rounded-md text-gray-600">
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="is_primary" checked class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                <label for="is_primary" class="ml-2 block text-sm text-gray-900">Jadikan Alamat Utama</label>
            </div>

            <button type="button" onclick="submitAddress()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-200">
                Kirim ke Backend API
            </button>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Koordinat awal (Default Jakarta: -6.2088, 106.8456)
        const defaultLat = -6.229728;
        const defaultLng = 106.805721;

        // Set value awal ke input readonly
        document.getElementById('latitude').value = defaultLat;
        document.getElementById('longitude').value = defaultLng;

        // Inisialisasi Peta Leaflet
        const map = L.map('map').setView([defaultLat, defaultLng], 13);

        // Load Tile Layer dari OpenStreetMap gratis
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Tambahkan Marker yang bisa di-drag/geser
        const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        // Event ketika marker selesai digeser
        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });

        // Event ketika peta diklik (pindahkan marker otomatis)
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });

        // Fungsi mengupdate nilai di form input
        // ==========================================
// GEOCODING (Alamat -> Koordinat) - STRICT MODE
// ==========================================
async function updateCoordsFromAddress() {
    const fullAddress = document.getElementById('full_address').value;
    const city = document.getElementById('city').value;
    const searchQuery = `${fullAddress}, ${city}`;

    if (fullAddress.length < 5) return;

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=1`);
        const results = await response.json();

        if (results && results.length > 0) {
            const topResult = results[0];
            const lat = parseFloat(topResult.lat);
            const lng = parseFloat(topResult.lon);

            // Jika koordinat pas dan ditemukan
            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);

            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);
        } else {
            // JIKA GAGAL: Langsung kosongkan koordinat agar backend menolak (karena database NOT NULL)
            document.getElementById('latitude').value = "";
            document.getElementById('longitude').value = "";
            alert("Koordinat tidak ditemukan untuk alamat tersebut! Mohon geser pin di peta secara manual.");
        }
    } catch (error) {
        console.error('Gagal mencari koordinat alamat:', error);
    }
}

// ==========================================
// VALIDASI TAMBAHAN SEBELUM SUBMIT
// ==========================================
async function submitAddress() {
    const token = document.getElementById('auth_token').value.trim();
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!token) {
        alert('Mohon isi Bearer Token terlebih dahulu!');
        return;
    }

    // Blokir pengiriman jika koordinat kosong/gagal didapatkan
    if (!lat || !lng) {
        alert('Gagal mengirim! Koordinat tidak valid atau belum ditentukan dari peta.');
        return;
    }

    const payload = {
        label: document.getElementById('label').value,
        recipient_name: document.getElementById('recipient_name').value,
        phone_number: document.getElementById('phone_number').value,
        full_address: document.getElementById('full_address').value,
        city: document.getElementById('city').value,
        postal_code: document.getElementById('postal_code').value,
        is_primary: document.getElementById('is_primary').checked,
        latitude: parseFloat(lat),
        longitude: parseFloat(lng)
    };

    try {
        const response = await fetch('/api/v1/order/addresses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.ok) {
            alert('Sukses! Alamat baru berhasil disimpan.');
            console.log('Success:', result);
        } else {
            // Jika double, database akan merespon error dan ditangkap di sini
            if (response.status === 500 || result.message.includes('Duplicate')) {
                alert('Gagal! Alamat dengan label dan lokasi ini sudah terdaftar (Double).');
            } else {
                alert(`Gagal (${response.status})! Periksa validasi.`);
            }
            console.error('Error Response:', result);
        }
    } catch (error) {
        alert('Terjadi kesalahan koneksi.');
    }
}
    </script>
</body>
</html>
