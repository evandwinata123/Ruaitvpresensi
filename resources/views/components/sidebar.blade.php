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

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('dashboard'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-home w-5 text-center {{ request()->routeIs('dashboard') ? 'text-indigo-300' : '' }}"></i>
            <span>Beranda</span>
        </a>
        <a href="#" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300">
            <i class="fas fa-clipboard-check w-5 text-center"></i>
            <span>Riwayat Presensi</span>
        </a>
        
        <a href="{{ route('leave.index') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('leave.*') ? 'active' : 'text-slate-300' }}"
            @if(request()->routeIs('leave.*'))
                style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
            @endif
        >
            <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('leave.*') ? 'text-indigo-300' : '' }}"></i>
            <span>Pengajuan Izin/Cuti</span>
        </a>
        
            <a href="{{ route('profile.index') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('profile.*') ? 'active' : 'text-slate-300' }}"
                @if(request()->routeIs('profile.*'))
                    style="background: rgba(255, 255, 255, 0.15); border-right: 3px solid #667eea;"
                @endif
            >
                <i class="fas fa-user-circle w-5 text-center {{ request()->routeIs('profile.*') ? 'text-indigo-300' : '' }}"></i>
                <span>Profil Saya</span>
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
        <a href="{{ route('dashboard') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-home w-5 text-center {{ request()->routeIs('dashboard') ? 'text-indigo-300' : '' }}"></i>
            <span>Beranda</span>
        </a>
        <a href="#" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium text-slate-300">
            <i class="fas fa-clipboard-check w-5 text-center"></i>
            <span>Riwayat Presensi</span>
        </a>
        
        <a href="{{ route('leave.index') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('leave.*') ? 'active' : 'text-slate-300' }}">
            <i class="fas fa-file-alt w-5 text-center {{ request()->routeIs('leave.*') ? 'text-indigo-300' : '' }}"></i>
            <span>Pengajuan Izin/Cuti</span>
        </a>


        
            <a href="{{ route('profile.index') }}" class="nav-link flex items-center space-x-3 px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('profile.*') ? 'active' : 'text-slate-300' }}">
                <i class="fas fa-user-circle w-5 text-center {{ request()->routeIs('profile.*') ? 'text-indigo-300' : '' }}"></i>
                <span>Profil Saya</span>
            </a>
        
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