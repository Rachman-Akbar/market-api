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
            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 mb-4">
                <label class="block text-sm font-bold text-amber-800 mb-1">Bearer Token (Untuk Auth API)</label>
                <input type="text" id="auth_token" placeholder="Masukkan token Anda di sini" 
                       class="mt-1 block w-full p-2 border border-amber-300 rounded-md shadow-sm bg-white text-sm font-mono">
            </div>

            <div class="grid grid-cols-2 gap-4">
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
                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap (Jalan/No)</label>
                <textarea id="full_address" rows="2" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" placeholder="Mengambil alamat dari peta otomatis..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Catatan Alamat <span class="text-gray-400 text-xs">(Optional)</span></label>
                <input type="text" id="notes" placeholder="Contoh: Blok C3, Dekat Masjid Al-Ikhlas, Pagar Hitam" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kota / Kabupaten</label>
                    <input type="text" id="city" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Pos</label>
                    <input type="text" id="postal_code" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
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
       // Memulai dengan nilai kosong/null (Tidak ada asumsi lokasi default)
let currentLat = null;
let currentLng = null;

// Inisialisasi peta secara global, tapi arahkan pandangan awal ke Indonesia secara luas (zoom jauh)
const map = L.map('map').setView([-2.548926, 118.014863], 4);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Marker dibuat tanpa posisi awal (belum melekat di peta)
const marker = L.marker([0, 0], { draggable: true });

// ==========================================
// OPTIONAL: AMBIL GPS ASLI (Jika User Mengizinkan)
// ==========================================
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        (position) => {
            currentLat = position.coords.latitude;
            currentLng = position.coords.longitude;
            
            // Pasang marker dan fokuskan peta hanya karena GPS terdeteksi asli
            marker.setLatLng([currentLat, currentLng]).addTo(map);
            map.setView([currentLat, currentLng], 16);
            updateAddressFromCoords(currentLat, currentLng);
        },
        () => {
            console.log("User menolak GPS. Menunggu user mengetik alamat atau klik peta secara manual.");
        }
    );
}

// ==========================================
// REVERSE GEOCODING (Klik/Geser Peta -> Form Alamat)
// ==========================================
async function updateAddressFromCoords(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
            headers: { 'User-Agent': 'MarketplaceTestingApp/1.0' }
        });
        const data = await response.json();

        if (data) {
            document.getElementById('full_address').value = data.display_name || '';
            const addr = data.address;
            document.getElementById('city').value = addr.city || addr.town || addr.municipality || addr.city_district || addr.state || '';
            document.getElementById('postal_code').value = addr.postcode || '';
        }
    } catch (error) {
        console.error('Gagal memuat alamat dari koordinat peta:', error);
    }
}

// Event geser pin
marker.on('dragend', function (e) {
    const position = marker.getLatLng();
    updateAddressFromCoords(position.lat, position.lng);
});

// Event klik peta (Membuat pin baru di area nyata yang diklik)
map.on('click', function (e) {
    marker.setLatLng(e.latlng).addTo(map);
    updateAddressFromCoords(e.latlng.lat, e.latlng.lng);
});


// ==========================================
// GEOCODING STRICT (Ketik Alamat -> Cari Koordinat Pas)
// ==========================================
async function updateCoordsFromAddress() {
    const fullAddress = document.getElementById('full_address').value.trim();
    const city = document.getElementById('city').value.trim();
    const searchQuery = `${fullAddress}, ${city}`;

    // Jangan cari jika input terlalu pendek
    if (fullAddress.length < 5) return; 

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=1`, {
            headers: { 'User-Agent': 'MarketplaceTestingApp/1.0' }
        });
        const results = await response.json();

        // JIKA KOORDINAT PAS & DITEMUKAN DI DUNIA NYATA
        if (results && results.length > 0) {
            const lat = parseFloat(results[0].lat);
            const lng = parseFloat(results[0].lon);

            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);

            // Munculkan pin dan fokuskan peta ke titik tersebut
            marker.setLatLng([lat, lng]).addTo(map);
            map.setView([lat, lng], 16);
        } else {
            // TOTAL REJECTION JIKA KOORDINAT TIDAK PAS
            document.getElementById('latitude').value = "";
            document.getElementById('longitude').value = "";
            
            // Hapus pin dari peta karena lokasinya gaib/tidak ketemu
            map.removeLayer(marker); 
            
            alert("⚠️ ALAMAT TIDAK DIKENALI! Koordinat tidak dapat ditemukan secara akurat. Mohon perbaiki teks alamat atau pilih langsung titiknya di peta.");
        }
    } catch (error) {
        console.error('Gagal mencari koordinat:', error);
    }
}

// Trigger pencarian saat user selesai mengetik alamat atau kota
document.getElementById('full_address').addEventListener('blur', updateCoordsFromAddress);
document.getElementById('city').addEventListener('blur', updateCoordsFromAddress);


// ==========================================
// SUBMIT TO BACKEND (STRICT VALIDATION)
// ==========================================
async function submitAddress() {
    const token = document.getElementById('auth_token').value.trim();
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!token) { 
        alert('❌ Gagal: Mohon isi Bearer Token!'); 
        return; 
    }
    
    // Validasi keras: Jika koordinat kosong, request tidak akan pernah dikirim ke backend
    if (!lat || !lng || lat === "" || lng === "") { 
        alert('❌ Gagal: Koordinat kosong atau tidak valid! Alamat harus ter-mapping dengan benar di peta dunia nyata sebelum disimpan.'); 
        return; 
    }

    const payload = {
        label: document.getElementById('label').value,
        recipient_name: document.getElementById('recipient_name').value,
        phone_number: document.getElementById('phone_number').value,
        full_address: document.getElementById('full_address').value,
        notes: document.getElementById('notes').value,
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
            alert('✅ Sukses: Alamat asli dan koordinat akurat berhasil disimpan ke database!');
            console.log(result);
        } else {
            alert(`❌ Gagal (${response.status}): Terjadi kesalahan validasi backend atau alamat duplikat.`);
            console.error(result);
        }
    } catch (error) {
        alert('❌ Gagal: Terjadi kesalahan koneksi jaringan.');
        console.error(error);
    }
}
    </script>
</body>
</html>