<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Presensi - Admin</title>
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
                    <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-clipboard-check text-amber-500 mr-2"></i>Review Presensi</h1>
                    <p class="text-slate-500 mt-1 text-sm">Persetujuan atau penolakan presensi masuk & pulang hari ini</p>
                </div>
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i><p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                </div>
                @endif
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
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($attendances as $att)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">{{ strtoupper(substr($att->user->name, 0, 1)) }}</div>
                                            <span class="text-sm font-medium text-slate-800">{{ $att->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $att->tanggal->isoFormat('D MMMM YYYY') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium {{ $att->check_in_time ? 'text-green-600' : 'text-slate-400' }}">
                                            {{ $att->check_in_time ? $att->check_in_time->format('H:i') : '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium {{ $att->check_out_time ? 'text-red-600' : 'text-slate-400' }}">
                                            {{ $att->check_out_time ? $att->check_out_time->format('H:i') : '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusClass = match($att->status) {
                                                'hadir' => 'bg-green-100 text-green-700',
                                                'izin' => 'bg-blue-100 text-blue-700',
                                                'sakit' => 'bg-red-100 text-red-700',
                                                'alpha' => 'bg-slate-100 text-slate-700',
                                                default => 'bg-amber-100 text-amber-700',
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }}">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <form action="{{ route('admin.review.approve', $att->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-green-500 text-white rounded-lg text-xs font-medium hover:bg-green-600 transition-all" title="Setujui">
                                                    <i class="fas fa-check mr-1"></i>Setujui
                                                </button>
                                            </form>
                                            <button type="button" onclick="openRejectModal({{ $att->id }}, '{{ $att->user->name }}')" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-medium hover:bg-red-600 transition-all" title="Tolak">
                                                <i class="fas fa-times mr-1"></i>Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-slate-400">
                                        <i class="fas fa-inbox text-3xl mb-2"></i>
                                        <p class="text-sm">Belum ada data presensi hari ini</p>
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

    <!-- Modal Tolak Presensi -->
    <div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center" onclick="closeRejectModal(event)">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-white font-bold flex items-center space-x-2">
                        <i class="fas fa-times-circle"></i>
                        <span>Tolak Presensi</span>
                    </h3>
                    <button type="button" onclick="closeRejectModal()" class="text-white/80 hover:text-white p-1">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-xl">
                        <i class="fas fa-user text-red-400"></i>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Karyawan</p>
                            <p class="text-sm font-bold text-slate-800" id="rejectEmployeeName">-</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            <i class="fas fa-comment text-red-400 mr-1"></i>Keterangan / Alasan Penolakan
                        </label>
                        <textarea name="keterangan" rows="3" required
                            placeholder="Masukkan alasan mengapa presensi ditolak..."
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-red-400 focus:border-transparent outline-none transition-all resize-none"></textarea>
                        <p class="text-xs text-slate-400 mt-1">Keterangan ini akan terlihat oleh karyawan</p>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeRejectModal()" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-medium hover:from-red-600 hover:to-rose-700 transition-all shadow-lg shadow-red-500/20">
                            <i class="fas fa-times mr-1"></i>Tolak Presensi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const s=document.getElementById('mobileSidebar'),o=document.getElementById('mobileOverlay');
            s.classList.contains('-translate-x-full')?(s.classList.remove('-translate-x-full'),o.classList.remove('hidden'),document.body.style.overflow='hidden'):(s.classList.add('-translate-x-full'),o.classList.add('hidden'),document.body.style.overflow='');
        }
        document.addEventListener('DOMContentLoaded',()=>{const m=document.getElementById('mobileMenuBtn');if(m)m.addEventListener('click',toggleMobileMenu);});

        function openRejectModal(id, name) {
            document.getElementById('rejectForm').action = '/admin/review/' + id + '/reject';
            document.getElementById('rejectEmployeeName').textContent = name;
            document.getElementById('rejectModal').classList.remove('hidden');
            document.getElementById('rejectModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeRejectModal(event) {
            if (event && event.target !== event.currentTarget) return;
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectModal').classList.remove('flex');
            document.body.style.overflow = '';
        }
    </script>
</body>
</html>