<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Firebase Auth</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col items-center justify-center font-sans">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md text-center">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Firebase Auth Testing</h2>

        <button id="btn-google-login" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-50 transition cursor-pointer">
            <svg class="w-6 h-6" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Sign in with Google
        </button>

        <div class="mt-8 text-left hidden" id="log-container">
            <h3 class="text-sm font-semibold text-gray-500 mb-2">Response Log:</h3>
            <pre id="log-output" class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs overflow-x-auto max-h-60"></pre>
        </div>
    </div>

    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-app.js";
        import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/10.8.0/firebase-auth.js";

        // TODO: Ganti dengan konfigurasi Firebase Project Anda sendiri
  const firebaseConfig = {
  apiKey: "AIzaSyBY5kse5sDeMCIU0UXVheWKUHhpSFBGGCw",
  authDomain: "marketplace-village.firebaseapp.com",
  databaseURL: "https://marketplace-village-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "marketplace-village",
  storageBucket: "marketplace-village.firebasestorage.app",
  messagingSenderId: "698326217840",
  appId: "1:698326217840:web:e3bb3a1a6eb4da25f8b0b5"
};

        // Inisialisasi Firebase
        const app = initializeApp(firebaseConfig);
        const auth = getAuth(app);
        const provider = new GoogleAuthProvider();

        // Elemen DOM
        const btnLogin = document.getElementById('btn-google-login');
        const logContainer = document.getElementById('log-container');
        const logOutput = document.getElementById('log-output');

        // Helper untuk mencetak log ke layar
        function showLog(title, data) {
            logContainer.classList.remove('hidden');
            logOutput.innerText = `[${title}]\n` + JSON.stringify(data, null, 2);
        }

        // Event Listener Tombol Login
        btnLogin.addEventListener('click', async () => {
            try {
                // 1. Trigger Pop Up Google Sign-In dari Firebase
                const result = await signInWithPopup(auth, provider);
                const user = result.user;

                // 2. Ambil ID Token Firebase untuk dikirim ke Backend Laravel
                const idToken = await user.getIdToken();

                showLog("Firebase Login Success", {
                    name: user.displayName,
                    email: user.email,
                    uid: user.uid,
                    token_preview: idToken.substring(0, 30) + "..."
                });

                // 3. Kirim Token ke API Laravel Backend Anda
                sendTokenToBackend(idToken);

            } catch (error) {
                console.error(error);
                showLog("Firebase Error", { code: error.code, message: error.message });
            }
        });

        // Fungsi untuk menembak API Backend Laravel
        async function sendTokenToBackend(token) {
            const apiUrl = '/api/v1/identity/auth/firebase-login'; // Sesuaikan jika domain API berbeda

            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        firebase_token: token, // Kirimkan ID Token ini ke backend
                        device_name: "Browser Testing (Laravel Blade)"
                    })
                });

                const jsonResponse = await response.json();
                showLog("Laravel Backend API Response", jsonResponse);

            } catch (error) {
                console.error(error);
                showLog("Backend API Network Error", { message: error.message });
            }
        }
    </script>
</body>
</html>
