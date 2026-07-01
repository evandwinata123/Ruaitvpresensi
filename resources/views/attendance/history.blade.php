<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Presensi - Presensi Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }

        .sidebar-gradient { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }
        .nav-link { transition: all 0.2s ease; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); transform: translateX(4px); }
        .nav-link.active { background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea; }

        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .table-row-hover:hover { background-color: #f8fafc; }

        .month-btn {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .month-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .month-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.25rem;
            border: 1px solid #e2e8f0;
        }

        .gradient-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

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
        .status-badge.terlambat::before { background-color: #f97316; }

        /* Print styles */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-area { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
        }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex min-h-screen">
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 lg:ml-72">
            <!-- Top Navigation -->
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm no-print">
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
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-slate-800">
                                <i class="fas fa-clipboard-check text-indigo-500 mr-2"></i>
                                Riwayat Presensi
                            </h1>
                            <p class="text-slate-500 mt-1 text-sm">Rekapan absensi per bulan</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Year Selector -->
                            <div class="relative">
                                <select id="yearSelect" class="appearance-none bg-white border border-slate-200 rounded-xl px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent shadow-sm">
                                    @for ($y = now()->year; $y >= now()->year - 2; $y--)
                                        <option value="{{ $y }}" {{ $y == $tahun ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                            </div>
                            <a href="{{ route('attendance.export.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="bg-white border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all shadow-sm flex items-center space-x-2">
                                <i class="fas fa-file-excel text-green-600"></i>
                                <span class="hidden sm:inline">Export Excel</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Month Grid -->
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3 mb-6 no-print">
                    @php
                        $bulanList = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $now = now();
                    @endphp
                    @foreach ($bulanList as $num => $nama)
                        @php
                            $isActive = $num == $bulan;
                            $isFuture = ($tahun > $now->year) || ($tahun == $now->year && $num > $now->month);
                            $stats = $monthlyStats[$num] ?? ['hadir' => 0, 'total' => 0, 'persen' => 0];
                        @endphp
                        <a href="{{ route('attendance.history.page', ['bulan' => $num, 'tahun' => $tahun]) }}"
                           class="month-btn rounded-xl p-3 text-center border {{ $isActive ? 'active' : 'bg-white border-slate-200 hover:border-indigo-300' }} {{ $isFuture ? 'opacity-50 pointer-events-none' : '' }}"
                        >
                            <div class="text-sm font-semibold {{ $isActive ? 'text-white' : 'text-slate-700' }}">{{ substr($nama, 0, 3) }}</div>
                            <div class="text-xs {{ $isActive ? 'text-indigo-200' : 'text-slate-400' }} mt-1">
                                @if($isFuture)
                                    -
                                @else
                                    {{ $stats['hadir'] }}/{{ $stats['total'] }}
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="stat-card card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Hari</p>
                                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $summary['total_hari'] }}</p>
                                <p class="text-xs text-slate-400 mt-1">Hari kerja</p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-calendar text-indigo-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Hadir</p>
                                <p class="text-2xl font-bold text-green-600 mt-1">{{ $summary['hadir'] }}</p>
                                <p class="text-xs text-green-500 mt-1">{{ $summary['persen_hadir'] }}% kehadiran</p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Izin/Sakit</p>
                                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $summary['izin_sakit'] }}</p>
                                <p class="text-xs text-amber-500 mt-1">Total izin & sakit</p>
                            </div>
                            <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-file-alt text-amber-500 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Alpha</p>
                                <p class="text-2xl font-bold text-red-600 mt-1">{{ $summary['alpha'] }}</p>
                                <p class="text-xs text-red-500 mt-1">Tanpa keterangan</p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-times-circle text-red-500 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden print-area">
                    <div class="p-6 border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">
                                    Detail Presensi {{ $bulanNama }} {{ $tahun }}
                                </h3>
                                <p class="text-sm text-slate-500">Daftar presensi harian</p>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-slate-500">
                                <i class="fas fa-info-circle text-indigo-400"></i>
                                <span>{{ count($attendances) }} data</span>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Hari</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jam Masuk</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jam Pulang</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($attendances as $item)
                                    @php
                                        $statusClass = match($item->status) {
                                            'hadir' => 'bg-green-100 text-green-700',
                                            'terlambat' => 'bg-amber-100 text-amber-700',
                                            'izin' => 'bg-blue-100 text-blue-700',
                                            'sakit' => 'bg-red-100 text-red-700',
                                            'alpha' => 'bg-slate-100 text-slate-700',
                                            'cuti' => 'bg-indigo-100 text-indigo-700',
                                            default => 'bg-slate-100 text-slate-700'
                                        };
                                        $statusDot = match($item->status) {
                                            'hadir' => 'bg-green-500',
                                            'terlambat' => 'bg-amber-500',
                                            'izin' => 'bg-blue-500',
                                            'sakit' => 'bg-red-500',
                                            'alpha' => 'bg-slate-500',
                                            'cuti' => 'bg-indigo-500',
                                            default => 'bg-slate-500'
                                        };
                                    @endphp
                                    <tr class="table-row-hover">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                                                    <i class="fas fa-calendar-day text-slate-600"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-slate-800">
                                                        {{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMMM YYYY') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd') }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->check_in_time)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                                    <i class="fas fa-sign-in-alt mr-1 text-xs"></i>
                                                    {{ \Carbon\Carbon::parse($item->check_in_time)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-sm text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->check_out_time)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                                    <i class="fas fa-sign-out-alt mr-1 text-xs"></i>
                                                    {{ \Carbon\Carbon::parse($item->check_out_time)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-sm text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                                <span class="w-1.5 h-1.5 {{ $statusDot }} rounded-full mr-1.5"></span>
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-slate-500">{{ $item->keterangan ?? '-' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-12">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                                    <i class="fas fa-inbox text-slate-400 text-2xl"></i>
                                                </div>
                                                <p class="text-slate-500 font-medium">Belum ada data presensi</p>
                                                <p class="text-slate-400 text-sm mt-1">Tidak ditemukan riwayat presensi untuk bulan ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 no-print">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Keterangan Status:</span>
                        <span class="inline-flex items-center text-xs text-slate-600"><span class="w-2.5 h-2.5 bg-green-500 rounded-full mr-1.5"></span>Hadir</span>
                        <span class="inline-flex items-center text-xs text-slate-600"><span class="w-2.5 h-2.5 bg-amber-500 rounded-full mr-1.5"></span>Terlambat</span>
                        <span class="inline-flex items-center text-xs text-slate-600"><span class="w-2.5 h-2.5 bg-blue-500 rounded-full mr-1.5"></span>Izin</span>
                        <span class="inline-flex items-center text-xs text-slate-600"><span class="w-2.5 h-2.5 bg-red-500 rounded-full mr-1.5"></span>Sakit</span>
                        <span class="inline-flex items-center text-xs text-slate-600"><span class="w-2.5 h-2.5 bg-indigo-500 rounded-full mr-1.5"></span>Cuti</span>
                        <span class="inline-flex items-center text-xs text-slate-600"><span class="w-2.5 h-2.5 bg-slate-500 rounded-full mr-1.5"></span>Alpha</span>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="border-t border-slate-200 bg-white px-4 sm:px-6 lg:px-8 py-4 no-print">
                <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400">
                    <p>&copy; {{ date('Y') }} Presensi Online System. All rights reserved.</p>
                    <p>v1.0.0</p>
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Mobile menu toggle
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
        document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleMobileMenu);

        // Update current date
        function updateDate() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').textContent = now.toLocaleDateString('id-ID', options);
        }
        updateDate();

        // Year change handler - navigate to selected year
        document.getElementById('yearSelect')?.addEventListener('change', function() {
            const tahun = this.value;
            const bulan = {{ $bulan }};
            window.location.href = '{{ url("/attendance/history") }}' + '/' + bulan + '/' + tahun;
        });
    </script>
</body>
</html>