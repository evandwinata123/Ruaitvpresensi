<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .transition-all { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex min-h-screen">
        @include('components.sidebar')
        <div class="flex-1 lg:ml-72">
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <div class="flex items-center space-x-3 lg:hidden">
                        <button id="mobileMenuBtn" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600"><i class="fas fa-bars text-xl"></i></button>
                        <div class="flex items-center space-x-2"><div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center shadow-lg"><i class="fas fa-fingerprint text-white text-xs"></i></div><span class="font-bold text-slate-800">Presensi</span></div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="px-3 py-1 text-xs font-semibold bg-purple-100 text-purple-700 rounded-full"><i class="fas fa-shield-alt mr-1"></i>Admin</span>
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold shadow-md">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    </div>
                </div>
            </header>
            <main class="p-4 sm:p-6 lg:p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-chart-pie text-green-500 mr-2"></i>Rekap Absensi</h1>
                        <p class="text-slate-500 mt-1 text-sm">Ringkasan bulanan kehadiran seluruh staf & karyawan</p>
                    </div>
                </div>

                <!-- Filter Bulan -->
                <form method="GET" class="mb-6">
                    <div class="flex items-end space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Bulan</label>
                            <select name="bulan" class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                                @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tahun</label>
                            <select name="tahun" class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                                @foreach(range(now()->year, now()->year - 2) as $t)
                                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-search mr-1"></i>Tampilkan
                        </button>
                    </div>
                </form>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">NIP</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-green-600 uppercase tracking-wider">Hadir</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-blue-600 uppercase tracking-wider">Izin</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-red-600 uppercase tracking-wider">Sakit</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-600 uppercase tracking-wider">Alpha</th>
                                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-800 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($rekap as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $r['employee']->nip ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $r['employee']->name }}</td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 text-sm font-bold">{{ $r['totalHadir'] }}</span></td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold">{{ $r['totalIzin'] }}</span></td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-700 text-sm font-bold">{{ $r['totalSakit'] }}</span></td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-sm font-bold">{{ $r['totalAlpha'] }}</span></td>
                                    <td class="px-6 py-4 text-center"><span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold">{{ $r['totalHadir'] + $r['totalIzin'] + $r['totalSakit'] + $r['totalAlpha'] }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-slate-400">
                                        <i class="fas fa-chart-bar text-3xl mb-2"></i>
                                        <p class="text-sm">Belum ada data rekap untuk bulan ini</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        function toggleMobileMenu() {
            const s=document.getElementById('mobileSidebar'),o=document.getElementById('mobileOverlay');
            s.classList.contains('-translate-x-full')?(s.classList.remove('-translate-x-full'),o.classList.remove('hidden'),document.body.style.overflow='hidden'):(s.classList.add('-translate-x-full'),o.classList.add('hidden'),document.body.style.overflow='');
        }
        document.addEventListener('DOMContentLoaded',()=>{const m=document.getElementById('mobileMenuBtn');if(m)m.addEventListener('click',toggleMobileMenu);});
    </script>
</body>
</html>