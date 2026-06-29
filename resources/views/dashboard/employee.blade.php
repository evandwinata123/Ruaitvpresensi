<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Employee - Presensi Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Server time sent to frontend as reference -->
    <meta name="server-time" content="{{ now()->format('Y-m-d H:i:s') }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * { font-family: 'Inter', sans-serif; }

        .gradient-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }

        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .pulse-animation { animation: pulse 2s infinite; }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(34, 197, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .status-badge { position: relative; }
        .status-badge::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }
        .status-badge.hadir::before { background-color: #22c55e; }
        .status-badge.izin::before { background-color: #f59e0b; }
        .status-badge.sakit::before { background-color: #ef4444; }
        .status-badge.cuti::before { background-color: #3b82f6; }
        .status-badge.alpha::before { background-color: #9ca3af; }

        .table-row-hover:hover { background-color: #f8fafc; }

        .glass-card { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }

        .attendance-btn-checkin { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
        .attendance-btn-checkin:hover { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); transform: scale(1.02); }
        .attendance-btn-checkout { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .attendance-btn-checkout:hover { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); transform: scale(1.02); }

        .clock-display { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }

        .sidebar-gradient { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }

        .nav-link { transition: all 0.2s ease; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); transform: translateX(4px); }
        .nav-link.active { background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea; }

        /* Camera Modal Styles */
        #cameraOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        #cameraOverlay.active { display: flex; }
        #cameraOverlay .modal-content {
            background: #1e293b;
            border-radius: 1.5rem;
            padding: 1.5rem;
            width: 90%;
            max-width: 420px;
            position: relative;
        }
        #cameraOverlay video { width: 100%; border-radius: 1rem; background: #000; transform: scaleX(-1); }
        #cameraOverlay canvas { display: none; }
        #cameraOverlay .photo-preview { width: 100%; border-radius: 1rem; display: none; }
        .camera-btn { padding: 0.75rem 2rem; border-radius: 1rem; font-weight: 600; font-size: 0.95rem; transition: all 0.2s; }
        .camera-btn:hover { transform: scale(1.05); }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex min-h-screen">
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 lg:ml-72">
            <!-- Top Navigation -->
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <div class="flex items-center space-x-3 lg:hidden">
                        <button id="mobileMenuBtn" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                                <i class="fas fa-fingerprint text-white text-xs"></i>
                            </div>
                            <span class="font-bold text-slate-800">Presensi</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="hidden md:flex items-center space-x-2 text-sm text-slate-500">
                            <i class="fas fa-calendar-day text-indigo-400"></i>
                            <span id="currentDate"></span>
                        </div>

                        <button class="relative p-2 rounded-lg hover:bg-slate-100 text-slate-500">
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <div class="flex items-center space-x-3 pl-4 border-l border-slate-200">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <!-- Welcome Section -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        Selamat Datang, <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-purple-600">{{ Auth::user()->name }}</span> 👋
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Semoga harimu menyenangkan dan penuh semangat!</p>
                </div>

                <!-- Stats Cards - Real data from database -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Hadir (Bulan Ini)</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalHadir }}</p>
                                <p class="text-xs text-green-500 mt-1"><i class="fas fa-check-circle mr-1"></i>Data presensi</p>
                            </div>
                            <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('leave.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Izin (Bulan Ini)</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1 group-hover:text-amber-600">{{ $totalIzin }} <span class="text-lg text-slate-400">/ {{ $sisaIzin + $totalIzin }}</span></p>
                                <p class="text-xs text-amber-500 mt-1"><i class="fas fa-clock mr-1"></i>Sisa: {{ $sisaIzin }} izin</p>
                            </div>
                            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center group-hover:bg-amber-200 transition-all">
                                <i class="fas fa-clock text-amber-500 text-2xl"></i>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('leave.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Sakit (Bulan Ini)</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1 group-hover:text-red-600">{{ $totalSakit }}</p>
                                <p class="text-xs text-red-500 mt-1"><i class="fas fa-hospital mr-1"></i>Wajib surat dokter</p>
                            </div>
                            <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center group-hover:bg-red-200 transition-all">
                                <i class="fas fa-hospital text-red-500 text-2xl"></i>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('leave.index') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all block group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Sisa Cuti</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1 group-hover:text-blue-600">{{ $sisaCuti }}</p>
                                <p class="text-xs text-blue-500 mt-1"><i class="fas fa-calendar mr-1"></i>Dari 12 hari/tahun</p>
                            </div>
                            <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center group-hover:bg-blue-200 transition-all">
                                <i class="fas fa-umbrella-beach text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Attendance & Clock Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Clock & Attendance Button -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <!-- Digital Clock -->
                            <div class="clock-display p-6 text-center">
                                <p class="text-xs text-slate-400 uppercase tracking-widest mb-2">Waktu Sekarang</p>
                                <h2 class="text-4xl font-bold text-white mb-1" id="liveClock">--:--:--</h2>
                                <p class="text-slate-400 text-sm" id="liveDate"></p>
                            </div>

                            <!-- Attendance Status & Button -->
                            <div class="p-6 space-y-4">
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-3 h-3 bg-green-500 rounded-full pulse-animation"></div>
                                        <span class="text-sm font-medium text-slate-700">Status</span>
                                    </div>
                                    <span class="text-sm font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full" id="statusText">Belum Presensi</span>
                                </div>

                                <button id="checkinBtn" class="attendance-btn-checkin w-full py-4 px-6 rounded-xl text-white font-semibold text-lg shadow-lg shadow-green-500/30 transition-all flex items-center justify-center space-x-3">
                                    <i class="fas fa-sign-in-alt text-xl"></i>
                                    <span id="checkinText">Presensi Masuk</span>
                                </button>

                                <button id="checkoutBtn" disabled class="w-full py-4 px-6 rounded-xl text-white font-semibold text-lg bg-slate-300 cursor-not-allowed transition-all flex items-center justify-center space-x-3">
                                    <i class="fas fa-sign-out-alt text-xl"></i>
                                    <span id="checkoutText">Presensi Pulang</span>
                                </button>

                                <div class="flex items-center space-x-2 text-xs text-slate-400 justify-center">
                                    <i class="fas fa-map-marker-alt text-indigo-400"></i>
                                    <span id="locationStatus">Lokasi akan direkam saat presensi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Activity -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800">Aktivitas Hari Ini</h3>
                                    <p class="text-sm text-slate-500">Ringkasan aktivitas presensi terkini</p>
                                </div>
                                <a href="#" class="text-sm text-indigo-500 hover:text-indigo-700 font-medium">Lihat Semua <i class="fas fa-arrow-right ml-1"></i></a>
                            </div>

                            <div class="space-y-0">
                                <div class="flex items-start space-x-4 pb-6 relative">
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center z-10 relative" id="checkinIcon">
                                            <i class="fas fa-sign-in-alt text-green-600"></i>
                                        </div>
                                        <div class="w-0.5 h-full bg-green-200 absolute top-10"></div>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-slate-800">Presensi Masuk</p>
                                            <span class="text-xs text-slate-400" id="checkinTime">--:-- WIB</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5" id="checkinLocation">Belum melakukan presensi masuk</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-4">
                                    <div class="flex flex-col items-center">
                                        <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center z-10 relative" id="checkoutIcon">
                                            <i class="fas fa-sign-out-alt text-slate-400"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-semibold text-slate-800">Presensi Pulang</p>
                                            <span class="text-xs text-slate-400" id="checkoutTime">--:-- WIB</span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-0.5" id="checkoutLocation">Belum melakukan presensi pulang</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Attendance History -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Riwayat Presensi</h3>
                                <p class="text-sm text-slate-500">Data presensi 7 hari terakhir</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <select class="text-sm border border-slate-200 rounded-lg px-3 py-2 text-slate-600 bg-white focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none">
                                    <option>Bulan Ini</option>
                                    <option>Bulan Lalu</option>
                                    <option>7 Hari</option>
                                </select>
                                <button class="p-2 border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-500">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jam Masuk</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jam Pulang</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="historyTable">
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-slate-400">
                                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                                        <p class="text-sm">Memuat data...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 bg-white px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400">
                    <p>&copy; {{ date('Y') }} Presensi Online System. All rights reserved.</p>
                    <p>v1.0.0</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Camera Modal - Selfie -->
    <div id="cameraOverlay">
        <div class="modal-content">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-bold text-lg" id="cameraTitle">Ambil Selfie</h3>
                <button onclick="closeCamera()" class="text-slate-400 hover:text-white p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="mb-3 flex items-center justify-center space-x-2 text-sm" id="modalLocationStatus">
                <i class="fas fa-map-marker-alt text-indigo-400"></i>
                <span class="text-slate-300">Mengambil lokasi saat ini...</span>
                <i class="fas fa-spinner fa-spin text-indigo-400"></i>
            </div>
            <div id="cameraContainer">
                <video id="video" autoplay playsinline></video>
            </div>
            <img id="photoPreview" class="photo-preview" alt="Preview selfie">

            <div id="cameraFallback" style="display:none" class="text-center py-10">
                <div class="text-slate-400">
                    <i class="fas fa-exclamation-triangle fa-3x mb-4 text-amber-400"></i>
                    <p class="text-sm text-white font-medium mb-1">Kamera tidak dapat diakses</p>
                    <p class="text-xs text-slate-400 px-4">
                        Pastikan Anda mengizinkan akses kamera di browser.
                        <br>Akses melalui <strong>http://localhost:8000</strong> (bukan 127.0.0.1).
                    </p>
                    <button onclick="closeCamera()" class="mt-4 camera-btn bg-slate-600 text-white hover:bg-slate-500">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            </div>

            <div class="flex justify-center gap-4 mt-4">
                <button id="captureBtn" onclick="capturePhoto()" class="camera-btn bg-white text-slate-800 hover:bg-slate-200">
                    <i class="fas fa-camera mr-2"></i>Ambil Foto
                </button>
                <button id="retakeBtn" onclick="retakePhoto()" class="camera-btn bg-slate-600 text-white hover:bg-slate-500" style="display:none">
                    <i class="fas fa-redo mr-2"></i>Ulangi
                </button>
                <button id="confirmBtn" onclick="confirmPhoto()" class="camera-btn bg-indigo-500 text-white hover:bg-indigo-600" style="display:none">
                    <i class="fas fa-check mr-2"></i>Gunakan
                </button>
            </div>
            <canvas id="canvas" width="640" height="480"></canvas>
        </div>
    </div>

    <script>
        let currentAction = null;
        let capturedPhoto = null;
        let userLatitude = null;
        let userLongitude = null;
        let stream = null;
        let timeOffset = 0;

        function initServerTime() {
            const serverTimeStr = document.querySelector('meta[name="server-time"]').content;
            const serverDate = new Date(serverTimeStr.replace(' ', 'T') + '+07:00');
            const browserNow = new Date();
            timeOffset = serverDate.getTime() - browserNow.getTime();
        }

        function updateClock() {
            const now = new Date(new Date().getTime() + timeOffset);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            document.getElementById('liveClock').textContent = `${hours}:${minutes}:${seconds}`;
            document.getElementById('liveDate').textContent = now.toLocaleDateString('id-ID', options);
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
        }

        function toggleMobileMenu() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('mobileOverlay');
            const isOpen = sidebar.classList.contains('-translate-x-full');
            if (isOpen) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        document.getElementById('mobileMenuBtn').addEventListener('click', toggleMobileMenu);

        function getLocation(showSpinner = false) {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    const msg = 'Geolokasi tidak didukung browser';
                    if (!showSpinner) {
                        document.getElementById('locationStatus').innerHTML = `<i class="fas fa-exclamation-triangle text-red-400 mr-1"></i> ${msg}`;
                    }
                    resolve(null);
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLatitude = position.coords.latitude;
                        userLongitude = position.coords.longitude;
                        if (!showSpinner) {
                            document.getElementById('locationStatus').innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i> Lokasi: ${userLatitude.toFixed(4)}, ${userLongitude.toFixed(4)}`;
                        }
                        resolve({ lat: userLatitude, lng: userLongitude });
                    },
                    function(error) {
                        const msg = 'Lokasi tidak terdeteksi: ' + error.message;
                        if (!showSpinner) {
                            document.getElementById('locationStatus').innerHTML = `<i class="fas fa-exclamation-triangle text-red-400 mr-1"></i> ${msg}`;
                        }
                        resolve(null);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }
        getLocation();

        function getFreshLocation() {
            const statusEl = document.getElementById('modalLocationStatus');
            statusEl.innerHTML = `<i class="fas fa-map-marker-alt text-indigo-400"></i> <span class="text-slate-300">Mengambil lokasi terkini...</span> <i class="fas fa-spinner fa-spin text-indigo-400"></i>`;

            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    statusEl.innerHTML = `<i class="fas fa-exclamation-triangle text-red-400"></i> <span class="text-red-300">Lokasi tidak didukung browser</span>`;
                    resolve(null);
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLatitude = position.coords.latitude;
                        userLongitude = position.coords.longitude;
                        statusEl.innerHTML = `<i class="fas fa-check-circle text-green-400"></i> <span class="text-green-300">Lokasi: ${userLatitude.toFixed(4)}, ${userLongitude.toFixed(4)}</span>`;
                        document.getElementById('locationStatus').innerHTML = `<i class="fas fa-check-circle text-green-500 mr-1"></i> Lokasi: ${userLatitude.toFixed(4)}, ${userLongitude.toFixed(4)}`;
                        resolve({ lat: userLatitude, lng: userLongitude });
                    },
                    function(error) {
                        statusEl.innerHTML = `<i class="fas fa-exclamation-triangle text-red-400"></i> <span class="text-red-300">Lokasi gagal: ${error.message}</span>`;
                        resolve(null);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }

        function openCamera(action) {
            currentAction = action;
            capturedPhoto = null;
            document.getElementById('cameraTitle').textContent = action === 'checkin' ? 'Selfie Presensi Masuk' : 'Selfie Presensi Pulang';
            document.getElementById('cameraOverlay').classList.add('active');
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('confirmBtn').style.display = 'none';
            document.getElementById('photoPreview').style.display = 'none';
            document.getElementById('cameraContainer').style.display = 'none';
            document.getElementById('cameraFallback').style.display = 'none';

            getFreshLocation();
            document.getElementById('cameraContainer').style.display = 'block';
            document.getElementById('captureBtn').style.display = 'inline-flex';
            document.getElementById('cameraFallback').style.display = 'none';
            startCamera();
        }

        function closeCamera() {
            document.getElementById('cameraOverlay').classList.remove('active');
            stopCamera();
        }

        async function startCamera() {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    alert('Kamera tidak dapat diakses. Gunakan HTTPS atau localhost.');
                    closeCamera();
                    return;
                }
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 480 }, audio: false });
                document.getElementById('video').srcObject = stream;
            } catch (err) {
                let msg = 'Tidak dapat mengakses kamera';
                if (err.name === 'NotAllowedError') msg = 'Izin kamera ditolak.';
                else if (err.name === 'NotFoundError') msg = 'Tidak ada kamera.';
                else if (err.name === 'NotReadableError') msg = 'Kamera dipakai aplikasi lain.';
                else msg += ': ' + err.message;
                alert(msg);
                closeCamera();
            }
        }

        function stopCamera() {
            if (stream) { stream.getTracks().forEach(track => track.stop()); stream = null; }
        }

        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);
            document.getElementById('cameraContainer').style.display = 'none';
            const preview = document.getElementById('photoPreview');
            preview.src = capturedPhoto;
            preview.style.display = 'block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('retakeBtn').style.display = 'inline-flex';
            document.getElementById('confirmBtn').style.display = 'inline-flex';
            stopCamera();
        }

        function retakePhoto() {
            capturedPhoto = null;
            document.getElementById('photoPreview').style.display = 'none';
            document.getElementById('cameraContainer').style.display = 'block';
            document.getElementById('captureBtn').style.display = 'inline-flex';
            document.getElementById('retakeBtn').style.display = 'none';
            document.getElementById('confirmBtn').style.display = 'none';
            document.getElementById('cameraFallback').style.display = 'none';
            startCamera();
        }

        function confirmPhoto() {
            if (!capturedPhoto) return;
            if (!userLatitude || !userLongitude) {
                if (!confirm('Lokasi belum terdeteksi. Lanjutkan tanpa lokasi?')) return;
            }
            const endpoint = currentAction === 'checkin' ? '{{ route("attendance.checkin") }}' : '{{ route("attendance.checkout") }}';
            document.getElementById('confirmBtn').innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            document.getElementById('confirmBtn').disabled = true;

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ photo: capturedPhoto, latitude: userLatitude || 0, longitude: userLongitude || 0 })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) { closeCamera(); showSuccess(data.message, data.data); loadStatus(); loadHistory(); }
                else { alert('Gagal: ' + data.message); }
            })
            .catch(err => { alert('Error: ' + err.message); })
            .finally(() => {
                document.getElementById('confirmBtn').innerHTML = '<i class="fas fa-check mr-2"></i>Gunakan';
                document.getElementById('confirmBtn').disabled = false;
            });
        }

        function showSuccess(message, data) {
            if (currentAction === 'checkin') {
                document.getElementById('checkinBtn').innerHTML = `<i class="fas fa-check-circle text-xl"></i> <span>Sudah Presensi (${data.time})</span>`;
                document.getElementById('checkinBtn').classList.remove('attendance-btn-checkin');
                document.getElementById('checkinBtn').classList.add('bg-green-500', 'cursor-default');
                document.getElementById('checkinBtn').style.background = '#22c55e';
                document.getElementById('checkinBtn').disabled = true;
                document.getElementById('checkinTime').textContent = data.time + ' WIB';
                document.getElementById('checkinLocation').textContent = '✅ Selfie & lokasi terekam';
                document.getElementById('checkinIcon').innerHTML = '<i class="fas fa-check-circle text-green-600 text-xl"></i>';
                document.getElementById('checkoutBtn').disabled = false;
                document.getElementById('checkoutBtn').classList.remove('bg-slate-300', 'cursor-not-allowed');
                document.getElementById('checkoutBtn').classList.add('attendance-btn-checkout');
                document.getElementById('statusText').textContent = 'Sudah Presensi Masuk';
                document.getElementById('statusText').classList.remove('text-green-600', 'bg-green-50');
                document.getElementById('statusText').classList.add('text-blue-600', 'bg-blue-50');
            } else {
                document.getElementById('checkoutBtn').innerHTML = `<i class="fas fa-check-circle text-xl"></i> <span>Sudah Pulang (${data.time})</span>`;
                document.getElementById('checkoutBtn').disabled = true;
                document.getElementById('checkoutBtn').style.background = '#ef4444';
                document.getElementById('checkoutBtn').classList.remove('attendance-btn-checkout');
                document.getElementById('checkoutBtn').classList.add('cursor-default');
                document.getElementById('checkoutTime').textContent = data.time + ' WIB';
                document.getElementById('checkoutLocation').textContent = '✅ Selfie & lokasi terekam';
                document.getElementById('checkoutIcon').innerHTML = '<i class="fas fa-check-circle text-red-500 text-xl"></i>';
                document.getElementById('checkoutIcon').parentElement.classList.remove('bg-slate-100');
                document.getElementById('checkoutIcon').parentElement.classList.add('bg-red-100');
                document.getElementById('statusText').textContent = 'Selesai (Sudah Pulang)';
                document.getElementById('statusText').classList.remove('text-green-600', 'bg-green-50', 'text-blue-600', 'bg-blue-50');
                document.getElementById('statusText').classList.add('text-slate-600', 'bg-slate-100');
            }
        }

        function loadStatus() {
            fetch('{{ route("attendance.status") }}', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    const d = data.data;
                    if (d.checked_in) {
                        document.getElementById('checkinBtn').innerHTML = `<i class="fas fa-check-circle text-xl"></i> <span>Sudah Presensi (${d.check_in_time})</span>`;
                        document.getElementById('checkinBtn').classList.remove('attendance-btn-checkin');
                        document.getElementById('checkinBtn').classList.add('bg-green-500', 'cursor-default');
                        document.getElementById('checkinBtn').style.background = '#22c55e';
                        document.getElementById('checkinBtn').disabled = true;
                        document.getElementById('checkinTime').textContent = d.check_in_time + ' WIB';
                        document.getElementById('checkinLocation').textContent = '✅ Selfie & lokasi terekam';
                        document.getElementById('checkinIcon').innerHTML = '<i class="fas fa-check-circle text-green-600 text-xl"></i>';
                        document.getElementById('statusText').textContent = 'Sudah Presensi Masuk';
                        document.getElementById('statusText').classList.remove('text-green-600', 'bg-green-50');
                        document.getElementById('statusText').classList.add('text-blue-600', 'bg-blue-50');
                        if (!d.checked_out) {
                            document.getElementById('checkoutBtn').disabled = false;
                            document.getElementById('checkoutBtn').classList.remove('bg-slate-300', 'cursor-not-allowed');
                            document.getElementById('checkoutBtn').classList.add('attendance-btn-checkout');
                        }
                        if (d.checked_out) {
                            document.getElementById('checkoutBtn').innerHTML = `<i class="fas fa-check-circle text-xl"></i> <span>Sudah Pulang (${d.check_out_time})</span>`;
                            document.getElementById('checkoutBtn').disabled = true;
                            document.getElementById('checkoutBtn').style.background = '#ef4444';
                            document.getElementById('checkoutBtn').classList.remove('attendance-btn-checkout');
                            document.getElementById('checkoutBtn').classList.add('cursor-default');
                            document.getElementById('checkoutTime').textContent = d.check_out_time + ' WIB';
                            document.getElementById('checkoutLocation').textContent = '✅ Selfie & lokasi terekam';
                            document.getElementById('checkoutIcon').innerHTML = '<i class="fas fa-check-circle text-red-500 text-xl"></i>';
                            document.getElementById('checkoutIcon').parentElement.classList.remove('bg-slate-100');
                            document.getElementById('checkoutIcon').parentElement.classList.add('bg-red-100');
                            document.getElementById('statusText').textContent = 'Selesai (Sudah Pulang)';
                            document.getElementById('statusText').classList.remove('text-green-600', 'bg-green-50', 'text-blue-600', 'bg-blue-50');
                            document.getElementById('statusText').classList.add('text-slate-600', 'bg-slate-100');
                        }
                    }
                }
            })
            .catch(err => console.error('Gagal load status:', err));
        }

        function loadHistory() {
            fetch('{{ route("attendance.history") }}', { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('historyTable');
                if (data.success && data.data.length > 0) {
                    tbody.innerHTML = data.data.map(item => {
                        const statusClass = { 'hadir': 'bg-green-100 text-green-700', 'terlambat': 'bg-amber-100 text-amber-700', 'izin': 'bg-blue-100 text-blue-700', 'sakit': 'bg-red-100 text-red-700', 'alpha': 'bg-slate-100 text-slate-700', 'cuti': 'bg-indigo-100 text-indigo-700' }[item.status] || 'bg-slate-100 text-slate-700';
                        const statusDot = { 'hadir': 'bg-green-500', 'terlambat': 'bg-amber-500', 'izin': 'bg-blue-500', 'sakit': 'bg-red-500', 'alpha': 'bg-slate-500', 'cuti': 'bg-indigo-500' }[item.status] || 'bg-slate-500';
                        return `<tr class="table-row-hover"><td class="px-6 py-4"><div class="flex items-center space-x-3"><div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center"><i class="fas fa-calendar-day text-slate-600"></i></div><div><p class="text-sm font-medium text-slate-800">${item.hari}, ${item.tanggal}</p></div></div></td><td class="px-6 py-4"><span class="text-sm font-medium text-slate-700">${item.check_in || '-'}</span></td><td class="px-6 py-4"><span class="text-sm font-medium text-slate-700">${item.check_out || '-'}</span></td><td class="px-6 py-4"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${statusClass}"><span class="w-1.5 h-1.5 ${statusDot} rounded-full mr-1.5"></span>${item.status.charAt(0).toUpperCase() + item.status.slice(1)}</span></td><td class="px-6 py-4"><span class="text-sm text-slate-500">${item.keterangan || '-'}</span></td></tr>`;
                    }).join('');
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center py-8 text-slate-400"><i class="fas fa-inbox text-3xl mb-2"></i><p class="text-sm">Belum ada riwayat presensi</p></td></tr>`;
                }
            })
            .catch(err => {
                document.getElementById('historyTable').innerHTML = `<tr><td colspan="5" class="text-center py-8 text-red-400"><p class="text-sm">Gagal memuat data</p></td></tr>`;
            });
        }

        document.getElementById('checkinBtn').addEventListener('click', function() { if (!this.disabled) openCamera('checkin'); });
        document.getElementById('checkoutBtn').addEventListener('click', function() { if (!this.disabled) openCamera('checkout'); });
        document.getElementById('cameraOverlay').addEventListener('click', function(e) { if (e.target === this) closeCamera(); });

        document.addEventListener('DOMContentLoaded', function() {
            initServerTime();
            updateClock();
            setInterval(updateClock, 1000);
            loadStatus();
            loadHistory();
        });
    </script>
</body>
</html>