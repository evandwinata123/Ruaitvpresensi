<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Presensi Online</title>
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

        .profile-photo-wrapper {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }

        .profile-photo-wrapper img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #e2e8f0;
        }

        .profile-photo-wrapper .photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .profile-photo-wrapper:hover .photo-overlay {
            opacity: 1;
        }

        .gradient-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }

        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }

        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
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
                            <span>{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
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
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-slate-800">
                        <i class="fas fa-user-circle text-indigo-500 mr-2"></i>Profil Saya
                    </h1>
                    <p class="text-slate-500 mt-1 text-sm">Kelola informasi profil dan pengaturan akun</p>
                </div>

                <!-- Success Message -->
                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center space-x-3">
                    <i class="fas fa-check-circle text-green-500 text-lg"></i>
                    <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                </div>
                @endif

                <!-- Error Message -->
                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start space-x-3">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-red-700">Terjadi kesalahan:</p>
                        <ul class="text-sm text-red-600 list-disc list-inside mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Photo Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 text-center">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">Foto Profil</h3>

                            <form action="{{ route('profile.photo') }}" method="POST" enctype="multipart/form-data" id="photoForm">
                                @csrf

                                <div class="profile-photo-wrapper mb-4">
                                    @if(Auth::user()->profile_photo)
                                        <img id="profilePreview" src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="Foto Profil">
                                    @else
                                        <img id="profilePreview" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=667eea&color=fff&size=150" alt="Foto Profil">
                                    @endif
                                    <div class="photo-overlay" onclick="document.getElementById('photoInput').click()">
                                        <i class="fas fa-camera text-white text-2xl"></i>
                                    </div>
                                </div>

                                <input type="file" id="photoInput" name="profile_photo" accept="image/*" class="hidden" onchange="previewPhoto(event)">

                                <p class="text-xs text-slate-400">Klik foto untuk mengganti<br>Maksimal 2MB (JPG, PNG, GIF)</p>
                            </form>
                        </div>
                    </div>

                    <!-- Profile Information Card -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Data Pribadi -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="gradient-header px-6 py-4">
                                <h3 class="text-white font-bold flex items-center space-x-2">
                                    <i class="fas fa-id-card"></i>
                                    <span>Data Pribadi</span>
                                </h3>
                            </div>

                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="p-6 space-y-5">
                                    <!-- NIP (readonly) -->
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-id-badge text-indigo-400 mr-1"></i>NIP
                                        </label>
                                        <input type="text" value="{{ Auth::user()->nip ?? '-' }}" readonly
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm cursor-not-allowed">
                                        <p class="text-xs text-slate-400 mt-1">NIP tidak dapat diubah</p>
                                    </div>

                                    <!-- Nama Lengkap -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-user text-indigo-400 mr-1"></i>Nama Lengkap
                                        </label>
                                        <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required
                                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-envelope text-indigo-400 mr-1"></i>Email
                                        </label>
                                        <input type="email" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required
                                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                    </div>

                                    <!-- Nomor HP -->
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-phone text-indigo-400 mr-1"></i>Nomor HP
                                        </label>
                                        <input type="text" id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}" placeholder="Contoh: 08123456789"
                                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                    </div>

                                    <!-- Alamat -->
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-map-marker-alt text-indigo-400 mr-1"></i>Alamat
                                        </label>
                                        <textarea id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap"
                                            class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all resize-none">{{ old('address', Auth::user()->address) }}</textarea>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                                        <button type="reset"
                                            class="px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                                            <i class="fas fa-undo mr-1"></i>Reset
                                        </button>
                                        <button type="submit"
                                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Ubah Password -->
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="gradient-header px-6 py-4">
                                <h3 class="text-white font-bold flex items-center space-x-2">
                                    <i class="fas fa-lock"></i>
                                    <span>Ubah Password</span>
                                </h3>
                            </div>

                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="p-6 space-y-5">
                                    <!-- Password Saat Ini -->
                                    <div>
                                        <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-key text-indigo-400 mr-1"></i>Password Saat Ini
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="current_password" name="current_password" required
                                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all pr-10">
                                            <button type="button" onclick="togglePassword('current_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Password Baru -->
                                    <div>
                                        <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-lock text-indigo-400 mr-1"></i>Password Baru
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="new_password" name="new_password" required minlength="8"
                                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all pr-10">
                                            <button type="button" onclick="togglePassword('new_password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-1">Minimal 8 karakter</p>
                                    </div>

                                    <!-- Konfirmasi Password Baru -->
                                    <div>
                                        <label for="new_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            <i class="fas fa-check-circle text-indigo-400 mr-1"></i>Konfirmasi Password Baru
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required minlength="8"
                                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all pr-10">
                                            <button type="button" onclick="togglePassword('new_password_confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Buttons -->
                                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                                        <button type="reset"
                                            class="px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                                            <i class="fas fa-undo mr-1"></i>Reset
                                        </button>
                                        <button type="submit"
                                            class="px-6 py-2.5 bg-gradient-to-r from-rose-500 to-pink-600 text-white rounded-xl text-sm font-medium hover:from-rose-600 hover:to-pink-700 transition-all shadow-lg shadow-rose-500/20">
                                            <i class="fas fa-key mr-1"></i>Ubah Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
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

        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('mobileMenuBtn');
            if (menuBtn) {
                menuBtn.addEventListener('click', toggleMobileMenu);
            }
        });

        // Preview photo before upload
        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                    // Auto submit the form when photo is selected
                    document.getElementById('photoForm').submit();
                };
                reader.readAsDataURL(file);
            }
        }

        // Toggle password visibility
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>