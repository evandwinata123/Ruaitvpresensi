<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Inter', sans-serif; }
        .gradient-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .transition-all { transition: all 0.3s cubic-bezier(0.4,0,0.2,1); }

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
                    <a href="{{ route('admin.employees') }}" class="text-sm text-indigo-500 hover:text-indigo-700 mb-2 inline-block"><i class="fas fa-arrow-left mr-1"></i>Kembali</a>
                    <h1 class="text-2xl font-bold text-slate-800"><i class="fas fa-user-edit text-indigo-500 mr-2"></i>Edit Profil Karyawan</h1>
                    <p class="text-slate-500 mt-1 text-sm">Kelola data profil dan foto karyawan</p>
                </div>
                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start space-x-3">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5"></i>
                    <div><p class="text-sm font-medium text-red-700">Terjadi kesalahan:</p><ul class="text-sm text-red-600 list-disc list-inside mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Photo Card -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 text-center">
                            <h3 class="text-lg font-bold text-slate-800 mb-6">Foto Profil</h3>

                            <div class="profile-photo-wrapper mb-4">
                                @if($employee->profile_photo)
                                    <img id="profilePreview" src="{{ asset('storage/' . $employee->profile_photo) }}" alt="Foto {{ $employee->name }}">
                                @else
                                    <img id="profilePreview" src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=667eea&color=fff&size=150" alt="Foto {{ $employee->name }}">
                                @endif
                                <div class="photo-overlay" onclick="document.getElementById('photoInput').click()">
                                    <i class="fas fa-camera text-white text-2xl"></i>
                                </div>
                            </div>

                            <p class="text-xs text-slate-400">Klik foto untuk mengganti<br>Maksimal 2MB (JPG, PNG, GIF)</p>
                        </div>
                    </div>

                    <!-- Form Data -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                            <div class="gradient-header px-6 py-4">
                                <h3 class="text-white font-bold"><i class="fas fa-id-card mr-2"></i>Data Pribadi: {{ $employee->name }}</h3>
                            </div>
                            <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf @method('PUT')

                                <input type="file" id="photoInput" name="profile_photo" accept="image/*" class="hidden" onchange="previewPhoto(event)">

                                <div class="p-6 space-y-5">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-id-badge text-indigo-400 mr-1"></i>NIP <span class="text-red-500">*</span></label>
                                            <input type="text" name="nip" value="{{ old('nip', $employee->nip) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-user text-indigo-400 mr-1"></i>Nama Lengkap <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" value="{{ old('name', $employee->name) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-envelope text-indigo-400 mr-1"></i>Email <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" value="{{ old('email', $employee->email) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-phone text-indigo-400 mr-1"></i>Nomor HP</label>
                                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="Contoh: 08123456789" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-calendar text-indigo-400 mr-1"></i>Tanggal Lahir</label>
                                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $employee->tanggal_lahir ? $employee->tanggal_lahir->format('Y-m-d') : '') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-lock text-indigo-400 mr-1"></i>Password Baru <span class="text-xs text-slate-400">(kosongkan jika tidak diubah)</span></label>
                                            <input type="password" name="password" minlength="8" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-check-circle text-indigo-400 mr-1"></i>Konfirmasi Password</label>
                                            <input type="password" name="password_confirmation" minlength="8" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5"><i class="fas fa-map-marker-alt text-indigo-400 mr-1"></i>Alamat</label>
                                        <textarea name="address" rows="2" placeholder="Masukkan alamat lengkap" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent outline-none transition-all resize-none">{{ old('address', $employee->address) }}</textarea>
                                    </div>
                                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                                        <a href="{{ route('admin.employees') }}" class="px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">Batal</a>
                                        <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl text-sm font-medium hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20"><i class="fas fa-save mr-1"></i>Simpan Perubahan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
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

        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>