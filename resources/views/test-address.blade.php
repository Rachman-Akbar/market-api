<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Hierarchical Address Testing</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 350px; width: 100%; border-radius: 0.5rem; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Simulasi Tambah Alamat Berjenjang</h2>
        <p class="text-xs text-gray-500 text-center mb-6">Sinkronisasi Peta & Form Wilayah (Standar Tokopedia)</p>

        <form id="addressForm" class="space-y-4" onsubmit="event.preventDefault();">
            <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 mb-4">
                <label class="block text-sm font-bold text-amber-800 mb-1">Bearer Token (Sanctum Device Session)</label>
                <input type="text" id="auth_token" placeholder="Masukkan token di sini untuk mendeteksi Buyer/Seller otomatis"
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

            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Hierarki Geografis (Saling Sinkron)</label>
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Negara</label>
                    <input type="text" id="country" value="Indonesia" class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Provinsi</label>
                    <input type="text" id="province" placeholder="Ketik provinsi..." class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Kota / Kabupaten</label>
                    <input type="text" id="city_or_regency" placeholder="Ketik kota/kabupaten..." class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Kecamatan</label>
                    <input type="text" id="district" placeholder="Ketik kecamatan..." class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Kelurahan / Desa</label>
                    <input type="text" id="subdistrict" placeholder="Ketik kelurahan..." class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Kode Pos</label>
                    <input type="text" id="postal_code" placeholder="Angka kode pos..." class="mt-1 block w-full p-2 border border-gray-300 rounded-md text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Alamat Lengkap (Nama Jalan, Blok, No Rumah)</label>
                <textarea id="full_address" rows="2" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" placeholder="Contoh: Jl. Diponegoro No.15, RT 01/RW 03"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Catatan Alamat / Patokan <span class="text-gray-400 text-xs">(Optional)</span></label>
                <input type="text" id="notes" placeholder="Contoh: Depan ruko warung kopi, pagar hitam" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
            </div>

            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <label class="block text-sm font-bold text-blue-800 mb-1">Komerce Destination ID (Subdistrik ID Logistik)</label>
                <input type="text" id="komerce_destination_id" placeholder="Otomatis terhitung dari kode area geografis"
                       class="mt-1 block w-full p-2 border border-blue-300 bg-white font-mono rounded-md shadow-sm text-sm">
            </div>

            <div class="pt-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pinpoint Lokasi Tracker (Geser/Klik)</label>
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

<button type="button" id="btn-submit" onclick="submitAddress()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition duration-200 flex items-center justify-center space-x-2">
    <svg id="loading-spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <span id="btn-text">Simpan Alamat ke Database</span>
</button>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>

let currentLat = null;
let currentLng = null;
let typingTimer;
const doneTypingInterval = 1200;

const map = L.map('map').setView([-2.548926, 118.014863], 4);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

const marker = L.marker([0, 0], { draggable: true });

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
        currentLat = pos.coords.latitude;
        currentLng = pos.coords.longitude;
        marker.setLatLng([currentLat, currentLng]).addTo(map);
        map.setView([currentLat, currentLng], 16);
        updateAddressFromCoords(currentLat, currentLng, true);
    });
}

// =======================================================
// 1. REVERSE GEOCODING (Pin Peta Digeser -> Pecah ke Form)
// =======================================================
async function updateAddressFromCoords(lat, lng, updateTextFields = true) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`, {
            headers: { 'User-Agent': 'MarketplaceTestingApp/1.0' }
        });
        const data = await response.json();

        if (data && data.address) {
            const addr = data.address;

            if (updateTextFields) {
                document.getElementById('country').value = addr.country || 'Indonesia';
                document.getElementById('province').value = addr.state || addr.region || '';

                // --- KOREKSI PINTAR UNTUK KECAMATAN & KOTA ---
                let rawCity = addr.city || addr.town || addr.municipality || '';
                let rawDistrict = addr.district || addr.suburb || addr.city_district || '';
                let rawCounty = addr.county || ''; // Biasanya berisi "Sidoarjo" jika Tulangan terdeteksi sebagai town

                // Jika Nominatim mendeteksi Kecamatan sebagai Town/City, kita tukar tempatnya
                if (rawCity && !rawDistrict) {
                    if (rawCounty) {
                        // Hilangkan kata "Kabupaten" atau "Regency" jika ada agar bersih
                        document.getElementById('city_or_regency').value = rawCounty.replace(/Kabupaten | Regency/gi, "");
                        document.getElementById('district').value = rawCity;
                    } else {
                        document.getElementById('city_or_regency').value = rawCity;
                        document.getElementById('district').value = rawCity; // Fallback kembar jika mentok
                    }
                } else {
                    document.getElementById('city_or_regency').value = rawCity || rawCounty.replace(/Kabupaten | Regency/gi, "");
                    document.getElementById('district').value = rawDistrict || rawCity;
                }

                // Isi Kelurahan / Desa
                document.getElementById('subdistrict').value = addr.village || addr.neighbourhood || addr.hamlet || addr.suburb || '';
                document.getElementById('postal_code').value = addr.postcode || '';
                document.getElementById('full_address').value = data.display_name || '';
            }

            document.getElementById('komerce_destination_id').value = addr.postcode ? "317" + addr.postcode : "3173031001";
        }
    } catch (error) {
        console.error('Gagal mengambil data koordinat:', error);
    }
}

marker.on('dragend', function() {
    updateAddressFromCoords(marker.getLatLng().lat, marker.getLatLng().lng, true);
});

map.on('click', function(e) {
    marker.setLatLng(e.latlng).addTo(map);
    updateAddressFromCoords(e.latlng.lat, e.latlng.lng, true);
});

// =======================================================
// 2. FORWARD GEOCODING (Hierarki diisi -> Mengisi Otomatis Alamat & Geser Titik)
// =======================================================
async function updateCoordsFromFields() {
    const province = document.getElementById('province').value.trim();
    const city = document.getElementById('city_or_regency').value.trim();
    const district = document.getElementById('district').value.trim();
    const subdistrict = document.getElementById('subdistrict').value.trim();
    const country = document.getElementById('country').value.trim() || "Indonesia";

    let searchParts = [];
    if (subdistrict) searchParts.push(subdistrict);
    if (district) searchParts.push(district);
    if (city) searchParts.push(city);
    if (province) searchParts.push(province);
    searchParts.push(country);

    if (city.length < 3 && province.length < 3) return;

    const formattedAddressText = searchParts.join(', ');
    document.getElementById('full_address').value = formattedAddressText;

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(formattedAddressText)}&limit=1`, {
            headers: { 'User-Agent': 'MarketplaceTestingApp/1.0' }
        });
        const results = await response.json();

        if (results && results.length > 0) {
            const lat = parseFloat(results[0].lat);
            const lng = parseFloat(results[0].lon);

            document.getElementById('latitude').value = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);

            marker.setLatLng([lat, lng]).addTo(map);
            map.setView([lat, lng], 15);

            updateAddressFromCoords(lat, lng, false);
        }
    } catch (error) {
        console.error('Gagal menyinkronkan peta dari data wilayah:', error);
    }
}

['province', 'city_or_regency', 'district', 'subdistrict'].forEach(id => {
    document.getElementById(id).addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(updateCoordsFromFields, doneTypingInterval);
    });
    document.getElementById(id).addEventListener('blur', updateCoordsFromFields);
});

// =======================================================
// 3. SUBMIT TO BACKEND (DENGAN LOGIKA ANTI-SPAM & LOADING STATE)
// =======================================================
async function submitAddress() {
    const token = document.getElementById('auth_token').value.trim();
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!token) { alert('❌ Gagal: Masukkan Bearer Token terlebih dahulu!'); return; }
    if (!lat || !lng) { alert('❌ Gagal: Koordinat peta wilayah kosong!'); return; }

    const btnSubmit = document.getElementById('btn-submit');
    const btnText = document.getElementById('btn-text');
    const loadingSpinner = document.getElementById('loading-spinner');

    // Aktifkan Loading State
    btnSubmit.disabled = true;
    btnSubmit.classList.add('opacity-75', 'cursor-not-allowed');
    btnText.innerText = "Menyimpan ke Database...";
    loadingSpinner.classList.remove('hidden');

    // FIX: Memastikan pengambilan value menggunakan id elemen yang benar dan memangkas spasi (trim)
    const payload = {
        label: document.getElementById('label').value.trim(),
        recipient_name: document.getElementById('recipient_name').value.trim(),
        phone_number: document.getElementById('phone_number').value.trim(),
        country: document.getElementById('country').value.trim() || 'Indonesia',
        province: document.getElementById('province').value.trim(),
        city_or_regency: document.getElementById('city_or_regency').value.trim(),
        district: document.getElementById('district').value.trim(), // <--- Pastikan ID ini ada di HTML input Kecamatan Anda
        subdistrict: document.getElementById('subdistrict').value.trim(),
        postal_code: document.getElementById('postal_code').value.trim(),
        full_address: document.getElementById('full_address').value.trim(),
        notes: document.getElementById('notes').value.trim() || null,
        latitude: parseFloat(lat),
        longitude: parseFloat(lng),
        komerce_destination_id: document.getElementById('komerce_destination_id').value.trim(),
        is_primary: document.getElementById('is_primary').checked ? 1 : 0 // Sesuaikan format boolean/integer backend
    };

    try {
        // Alamat URL disesuaikan dengan route versioning API Anda: /api/v1/order/addresses/
        const response = await fetch('/api/v1/order/addresses/', {
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
            alert('✅ Sukses: Alamat berjenjang berhasil disimpan ke DB!');
            console.log(result);
        } else {
            // Membantu menampilkan detail error validasi jika gagal lagi
            if (result.errors) {
                console.error('Detail Error Validasi Backend:', result.errors);
                alert(`❌ Validasi Gagal: ${Object.values(result.errors).flat().join(', ')}`);
            } else {
                alert(`❌ Gagal (${response.status}): ${result.message || 'Error internal.'}`);
            }
        }
    } catch (error) {
        alert('❌ Koneksi menuju backend gagal.');
        console.error(error);
    } finally {
        // Matikan Loading State
        btnSubmit.disabled = false;
        btnSubmit.classList.remove('opacity-75', 'cursor-not-allowed');
        btnText.innerText = "Simpan Alamat ke Database";
        loadingSpinner.classList.add('hidden');
    }
}
</script>
</body>
</html>
