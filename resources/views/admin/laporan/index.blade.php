<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Detail - Admin</title>
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
@include('components.sidebaradmin')
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
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-file-alt text-rose-500 mr-2"></i>Laporan Detail</h1>
                    <p class="text-slate-500 mt-1 text-sm">Seluruh rincian riwayat catatan presensi</p>
                </div>

                <!-- Filters -->
                <form method="GET" class="mb-6 p-5 bg-white rounded-2xl shadow-sm border border-slate-100">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Karyawan</label>
                            <select name="employee_id" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                                <option value="">Semua Karyawan</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->nip ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="dari" value="{{ request('dari') }}" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Sampai Tanggal</label>
                            <input type="date" name="sampai" value="{{ request('sampai') }}" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 mt-4">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-search mr-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.laporan') }}" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                            <i class="fas fa-undo mr-1"></i>Reset
                        </a>
                    </div>
                </form>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Karyawan</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jam Masuk</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jam Pulang</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($attendances as $att)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">{{ strtoupper(substr($att->user->name, 0, 1)) }}</div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800">{{ $att->user->name }}</p>
                                                <p class="text-xs text-slate-400">{{ $att->user->nip ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $att->tanggal->isoFormat('D MMMM YYYY') }}</td>
                                    <td class="px-6 py-4">
                                        @if($att->check_in_time)
                                        <span class="text-sm font-medium text-green-600">{{ $att->check_in_time->format('H:i') }}</span>
                                        @else
                                        <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($att->check_out_time)
                                        <span class="text-sm font-medium text-red-600">{{ $att->check_out_time->format('H:i') }}</span>
                                        @else
                                        <span class="text-sm text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $sc = match($att->status) {
                                                'hadir' => 'bg-green-100 text-green-700',
                                                'izin' => 'bg-blue-100 text-blue-700',
                                                'sakit' => 'bg-red-100 text-red-700',
                                                'alpha' => 'bg-slate-100 text-slate-700',
                                                default => 'bg-amber-100 text-amber-700',
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $sc }}">{{ ucfirst($att->status) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $att->keterangan ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-slate-400">
                                        <i class="fas fa-file-alt text-3xl mb-2"></i>
                                        <p class="text-sm">Tidak ada data laporan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($attendances->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $attendances->links() }}
                    </div>
                    @endif
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