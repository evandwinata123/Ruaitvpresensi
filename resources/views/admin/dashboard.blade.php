<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Presensi Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .gradient-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex min-h-screen">
        @include('components.sidebar')

        <div class="flex-1 lg:ml-72">
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
                            <span>{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full"><i class="fas fa-shield-alt mr-1"></i>Admin</span>
                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        <i class="fas fa-tachometer-alt text-indigo-500 mr-2"></i>Dashboard Admin
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Selamat datang, <span class="font-semibold text-indigo-600">{{ Auth::user()->name }}</span>. Kelola seluruh data presensi dan karyawan.</p>
                </div>

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                    <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Karyawan</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalKaryawan }}</p>
                                <p class="text-xs text-indigo-500 mt-1"><i class="fas fa-users mr-1"></i>Terdaftar</p>
                            </div>
                            <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-users text-indigo-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Presensi Hari Ini</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalPresensiHariIni }}</p>
                                <p class="text-xs text-green-500 mt-1"><i class="fas fa-check-circle mr-1"></i>Sudah presensi</p>
                            </div>
                            <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-fingerprint text-green-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Izin Hari Ini</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalIzinHariIni }}</p>
                                <p class="text-xs text-amber-500 mt-1"><i class="fas fa-clock mr-1"></i>Izin</p>
                            </div>
                            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-clock text-amber-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Sakit Hari Ini</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $totalSakitHariIni }}</p>
                                <p class="text-xs text-red-500 mt-1"><i class="fas fa-hospital mr-1"></i>Sakit</p>
                            </div>
                            <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-hospital text-red-500 text-2xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Cards -->
                <h2 class="text-lg font-bold text-slate-800 mb-4">Menu Utama</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Manajemen Karyawan -->
                    <a href="{{ route('admin.employees') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 card-hover transition-all group">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center group-hover:bg-indigo-200 transition-all">
                                <i class="fas fa-users-cog text-indigo-500 text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-indigo-600 transition-all">Manajemen Karyawan</h3>
                                <p class="text-sm text-slate-500 mt-1">Tambah, edit, dan kelola data profil staf & karyawan</p>
                                <span class="inline-flex items-center text-xs font-medium text-indigo-500 mt-3 group-hover:text-indigo-700">
                                    Kelola <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Review Presensi -->
                    <a href="{{ route('admin.review') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 card-hover transition-all group">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center group-hover:bg-amber-200 transition-all">
                                <i class="fas fa-clipboard-check text-amber-500 text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-amber-600 transition-all">Review Presensi</h3>
                                <p class="text-sm text-slate-500 mt-1">Persetujuan atau penolakan ajuan presensi masuk & pulang</p>
                                <span class="inline-flex items-center text-xs font-medium text-amber-500 mt-3 group-hover:text-amber-700">
                                    Review <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Rekap Absensi -->
                    <a href="{{ route('admin.rekap') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 card-hover transition-all group">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center group-hover:bg-green-200 transition-all">
                                <i class="fas fa-chart-pie text-green-500 text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-green-600 transition-all">Rekap Absensi</h3>
                                <p class="text-sm text-slate-500 mt-1">Ringkasan bulanan kehadiran seluruh staf & karyawan</p>
                                <span class="inline-flex items-center text-xs font-medium text-green-500 mt-3 group-hover:text-green-700">
                                    Lihat Rekap <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </a>

                    <!-- Laporan Detail -->
                    <a href="{{ route('admin.laporan') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 card-hover transition-all group">
                        <div class="flex items-start space-x-4">
                            <div class="w-16 h-16 bg-rose-100 rounded-2xl flex items-center justify-center group-hover:bg-rose-200 transition-all">
                                <i class="fas fa-file-alt text-rose-500 text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-rose-600 transition-all">Laporan Detail</h3>
                                <p class="text-sm text-slate-500 mt-1">Lihat seluruh rincian riwayat catatan presensi</p>
                                <span class="inline-flex items-center text-xs font-medium text-rose-500 mt-3 group-hover:text-rose-700">
                                    Lihat Laporan <i class="fas fa-arrow-right ml-1 text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </main>

            <footer class="border-t border-slate-200 bg-white px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400">
                    <p>&copy; {{ date('Y') }} Presensi Online System. All rights reserved.</p>
                    <p>v1.0.0</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
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
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('mobileMenuBtn');
            if (menuBtn) menuBtn.addEventListener('click', toggleMobileMenu);
        });
    </script>
</body>
</html>