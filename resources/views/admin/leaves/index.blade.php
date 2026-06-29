<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Perizinan - Admin</title>
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
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-file-alt text-blue-500 mr-2"></i>Manajemen Perizinan</h1>
                        <p class="text-slate-500 mt-1 text-sm">Persetujuan atau penolakan pengajuan izin, cuti, dan sakit</p>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mb-6">
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-amber-50 border border-amber-200 rounded-xl">
                        <i class="fas fa-clock text-amber-500"></i>
                        <span class="text-sm font-medium text-amber-700">Menunggu Review: <strong>{{ $totalPending }}</strong> pengajuan</span>
                    </div>
                </div>

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i><p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                </div>
                @endif

                <!-- Filters -->
                <div class="mb-6">
                    <form method="GET" class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cari Karyawan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-slate-400"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="NIP atau Nama..." class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Jenis</label>
                            <select name="type" class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                                <option value="">Semua</option>
                                <option value="izin" {{ request('type') == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="cuti" {{ request('type') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                                <option value="sakit" {{ request('type') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                            <select name="status" class="px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 outline-none">
                                <option value="">Semua</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                            <i class="fas fa-search mr-1"></i>Filter
                        </button>
                        @if(request('search') || request('type') || request('status'))
                        <a href="{{ route('admin.leaves') }}" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                            <i class="fas fa-undo mr-1"></i>Reset
                        </a>
                        @endif
                    </form>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Karyawan</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Alasan</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($leaveRequests as $leave)
                                @php
                                    $typeBadge = match($leave->type) {
                                        'izin' => 'bg-blue-100 text-blue-700',
                                        'cuti' => 'bg-purple-100 text-purple-700',
                                        'sakit' => 'bg-red-100 text-red-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    $typeIcon = match($leave->type) {
                                        'izin' => 'fa-clock',
                                        'cuti' => 'fa-umbrella-beach',
                                        'sakit' => 'fa-hospital',
                                        default => 'fa-file',
                                    };
                                    $statusBadge = match($leave->status) {
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'disetujui' => 'bg-green-100 text-green-700',
                                        'ditolak' => 'bg-red-100 text-red-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                    $statusIcon = match($leave->status) {
                                        'pending' => 'fa-clock',
                                        'disetujui' => 'fa-check-circle',
                                        'ditolak' => 'fa-times-circle',
                                        default => 'fa-question-circle',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">{{ strtoupper(substr($leave->user->name, 0, 1)) }}</div>
                                            <div>
                                                <p class="text-sm font-medium text-slate-800">{{ $leave->user->name }}</p>
                                                <p class="text-xs text-slate-400">{{ $leave->user->nip ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-medium {{ $typeBadge }}">
                                            <i class="fas {{ $typeIcon }}"></i>
                                            <span>{{ ucfirst($leave->type) }}</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-800">{{ $leave->start_date->isoFormat('D MMM') }} - {{ $leave->end_date->isoFormat('D MMM YYYY') }}</p>
                                        <p class="text-xs text-slate-400">{{ $leave->start_date->diffInDays($leave->end_date) + 1 }} hari</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-600 max-w-xs truncate" title="{{ $leave->alasan }}">{{ $leave->alasan }}</p>
                                        @if($leave->dokter_photo)
                                        <a href="{{ asset('storage/dokter/' . $leave->dokter_photo) }}" target="_blank" class="text-xs text-indigo-500 hover:text-indigo-700 mt-1 inline-block">
                                            <i class="fas fa-file-image mr-1"></i>Lihat Surat Dokter
                                        </a>
                                        @endif
                                        @if($leave->catatan_admin)
                                        <div class="mt-1 text-xs text-slate-400">
                                            <i class="fas fa-comment mr-1"></i>Catatan: {{ $leave->catatan_admin }}
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-medium {{ $statusBadge }}">
                                            <i class="fas {{ $statusIcon }}"></i>
                                            <span>{{ ucfirst($leave->status) }}</span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($leave->status === 'pending')
                                        <div class="flex items-center space-x-2">
                                            <form action="{{ route('admin.leaves.approve', $leave->id) }}" method="POST" onsubmit="return confirm('Setujui pengajuan {{ $leave->type }} dari {{ $leave->user->name }}?')">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-green-500 text-white rounded-lg text-xs font-medium hover:bg-green-600 transition-all">
                                                    <i class="fas fa-check mr-1"></i>Setujui
                                                </button>
                                            </form>
                                            <button type="button" onclick="openRejectModal({{ $leave->id }}, '{{ $leave->user->name }}', '{{ $leave->type }}')" class="px-3 py-1.5 bg-red-500 text-white rounded-lg text-xs font-medium hover:bg-red-600 transition-all">
                                                <i class="fas fa-times mr-1"></i>Tolak
                                            </button>
                                        </div>
                                        @elseif($leave->status === 'disetujui')
                                        <span class="text-xs text-green-600">
                                            <i class="fas fa-check-circle mr-1"></i>Disetujui oleh {{ $leave->approver->name ?? 'Admin' }}
                                            @if($leave->approved_at)
                                            <br>{{ $leave->approved_at->isoFormat('D MMM YYYY, HH:mm') }}
                                            @endif
                                        </span>
                                        @else
                                        <span class="text-xs text-red-600">
                                            <i class="fas fa-times-circle mr-1"></i>Ditolak
                                            @if($leave->approved_at)
                                            <br>{{ $leave->approved_at->isoFormat('D MMM YYYY, HH:mm') }}
                                            @endif
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-slate-400">
                                        <i class="fas fa-inbox text-3xl mb-2"></i>
                                        <p class="text-sm">Belum ada data pengajuan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($leaveRequests->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">
                        {{ $leaveRequests->links() }}
                    </div>
                    @endif
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Tolak Pengajuan -->
    <div id="rejectModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center" onclick="closeRejectModal(event)">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden" onclick="event.stopPropagation()">
            <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-white font-bold flex items-center space-x-2">
                        <i class="fas fa-times-circle"></i>
                        <span>Tolak Pengajuan</span>
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
                            <i class="fas fa-comment text-red-400 mr-1"></i>Keterangan / Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="catatan_admin" rows="3" required
                            placeholder="Masukkan alasan mengapa pengajuan ditolak..."
                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-red-400 focus:border-transparent outline-none transition-all resize-none"></textarea>
                        <p class="text-xs text-slate-400 mt-1">Catatan ini akan terlihat oleh karyawan</p>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeRejectModal()" class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl text-sm font-medium hover:from-red-600 hover:to-rose-700 transition-all shadow-lg shadow-red-500/20">
                            <i class="fas fa-times mr-1"></i>Tolak Pengajuan
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

        function openRejectModal(id, name, type) {
            document.getElementById('rejectForm').action = '/admin/leaves/' + id + '/reject';
            document.getElementById('rejectEmployeeName').textContent = name + ' (' + type + ')';
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