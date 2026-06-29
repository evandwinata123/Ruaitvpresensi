<style>
    .sidebar-gradient { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }
    .nav-link { transition: all 0.2s ease; }
    .nav-link:hover { background: rgba(255, 255, 255, 0.1); transform: translateX(4px); }
    .nav-link.active { background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea; }
</style>
<aside class="hidden lg:flex lg:flex-col w-72 sidebar-gradient text-white fixed h-full z-30">
    <!-- Logo & Brand -->
    <div class="px-6 py-8 border-b border-white/10">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-fingerprint text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold tracking-tight">Presensi</h1>
                <p class="text-xs text-slate-400 font-light">Online System</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <!-- Admin Section -->
        <div class="pt-2 pb-2">
            <div class="flex items-center px-4">
                <div class="w-1 h-4 bg-gradient-to-b from-indigo-400 to-purple-500 rounded-full mr-2"></div>
                <p class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Panel Admin</p>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('admin.dashboard'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-tachometer-alt w-5 text-center {{ request()->routeIs('admin.dashboard') ? 'text-indigo-300' : '' }}"></i>
            <span>Dashboard Admin</span>
        </a>
        <a href="{{ route('admin.employees') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.employees*') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('admin.employees*'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-users-cog w-5 text-center {{ request()->routeIs('admin.employees*') ? 'text-indigo-300' : '' }}"></i>
            <span>Manajemen Karyawan</span>
        </a>
        <a href="{{ route('admin.review') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.review') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('admin.review'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-clipboard-check w-5 text-center {{ request()->routeIs('admin.review') ? 'text-indigo-300' : '' }}"></i>
            <span>Review Presensi</span>
        </a>
        <a href="{{ route('admin.rekap') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.rekap') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('admin.rekap'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-chart-pie w-5 text-center {{ request()->routeIs('admin.rekap') ? 'text-indigo-300' : '' }}"></i>
            <span>Rekap Absensi</span>
        </a>
        <a href="{{ route('admin.laporan') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.laporan') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('admin.laporan'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('admin.laporan') ? 'text-indigo-300' : '' }}"></i>
            <span>Laporan Detail</span>
        </a>
        <a href="{{ route('admin.leaves') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.leaves*') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('admin.leaves*'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('admin.leaves*') ? 'text-indigo-300' : '' }}"></i>
            <span>Manajemen Perizinan</span>
        </a>

        
    </nav>

    <!-- Logout -->
    <div class="px-4 py-4 border-t border-white/10">
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium text-red-300 hover:text-red-200 hover:bg-red-500/10 w-full transition-all">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>

<!-- Mobile Sidebar -->
<div id="mobileOverlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
<div id="mobileSidebar" class="fixed top-0 left-0 h-full w-72 sidebar-gradient text-white z-40 transform -translate-x-full transition-transform duration-300 lg:hidden">
    <div class="px-6 py-8 border-b border-white/10">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                    <i class="fas fa-fingerprint text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">Presensi</h1>
                    <p class="text-xs text-slate-400 font-light">Online System</p>
                </div>
            </div>
            <button onclick="toggleMobileMenu()" class="p-2 hover:bg-white/10 rounded-lg">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <!-- Admin Section -->
        <div class="pt-2 pb-2">
            <div class="flex items-center px-4">
                <div class="w-1 h-4 bg-gradient-to-b from-indigo-400 to-purple-500 rounded-full mr-2"></div>
                <p class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Panel Admin</p>
            </div>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-tachometer-alt w-5 text-center {{ request()->routeIs('admin.dashboard') ? 'text-indigo-300' : '' }}"></i>
            <span>Dashboard Admin</span>
        </a>
        <a href="{{ route('admin.employees') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.employees*') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-users-cog w-5 text-center {{ request()->routeIs('admin.employees*') ? 'text-indigo-300' : '' }}"></i>
            <span>Manajemen Karyawan</span>
        </a>
        <a href="{{ route('admin.review') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.review') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-clipboard-check w-5 text-center {{ request()->routeIs('admin.review') ? 'text-indigo-300' : '' }}"></i>
            <span>Review Presensi</span>
        </a>
        <a href="{{ route('admin.rekap') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.rekap') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-chart-pie w-5 text-center {{ request()->routeIs('admin.rekap') ? 'text-indigo-300' : '' }}"></i>
            <span>Rekap Absensi</span>
        </a>
        <a href="{{ route('admin.laporan') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.laporan') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('admin.laporan') ? 'text-indigo-300' : '' }}"></i>
            <span>Laporan Detail</span>
        </a>

        <div class="pt-3">
            <a href="{{ route('profile.index') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('profile.*') ? 'active' : 'text-slate-300' }}">
                <i class="fas fa-user-circle w-5 text-center {{ request()->routeIs('profile.*') ? 'text-indigo-300' : '' }}"></i>
                <span>Profil Saya</span>
            </a>
        </div>
    </nav>
    <div class="px-4 py-4 border-t border-white/10">
        <form action="{{ route('auth.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium text-red-300 hover:text-red-200 hover:bg-red-500/10 w-full transition-all">
                <i class="fas fa-sign-out-alt w-5 text-center"></i>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</div>