<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin / Cuti - Presensi Online</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * { font-family: 'Inter', sans-serif; }

        .gradient-header {
            background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 50%, #ea580c 100%);
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        .sidebar-gradient {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }

        .nav-link { transition: all 0.2s ease; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); transform: translateX(4px); }
        .nav-link.active { background: rgba(255, 255, 255, 0.15); border-right: 3px solid #dc2626; }

        .type-card {
            cursor: pointer;
            transition: all 0.3s;
        }
        .type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .type-card.selected {
            border-color: #dc2626 !important;
            background: rgba(220, 38, 38, 0.05);
        }

        .btn-submit {
            background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.4);
        }

        input:focus, textarea:focus, select:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
        }

        .file-upload-area:hover {
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.03);
        }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex min-h-screen">
        @include('components.sidebar')

        <!-- Main Content -->
        <div class="flex-1 lg:ml-72">
            <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <div class="flex items-center space-x-3">
                        <button id="mobileMenuBtn" class="p-2 rounded-lg hover:bg-slate-100 text-slate-600 lg:hidden">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-lg font-bold text-slate-800">Pengajuan Izin / Cuti</h1>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                        </div>
                        <div class="w-9 h-9 bg-gradient-to-br from-red-400 to-red-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                <!-- Quota Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Sisa Izin (Bulan Ini)</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $izinRemaining }} <span class="text-lg text-slate-400">/ 5</span></p>
                                <p class="text-xs text-slate-400 mt-1">Reset setiap awal bulan</p>
                            </div>
                            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-clock text-amber-500 text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($izinRemaining / 5) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Sisa Cuti (Tahun Ini)</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $cutiRemaining }} <span class="text-lg text-slate-400">/ 12</span></p>
                                <p class="text-xs text-slate-400 mt-1">Reset setiap awal tahun</p>
                            </div>
                            <div class="w-14 h-14 bg-blue-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-umbrella-beach text-blue-500 text-2xl"></i>
                            </div>
                        </div>
                        <div class="mt-3 w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($cutiRemaining / 12) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 card-hover transition-all">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Sakit</p>
                                <p class="text-3xl font-bold text-slate-800 mt-1">&#8734;</p>
                                <p class="text-xs text-slate-400 mt-1">Wajib surat dokter</p>
                            </div>
                            <div class="w-14 h-14 bg-red-100 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-hospital text-red-500 text-2xl"></i>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-3">Tidak terbatas, wajib upload foto surat keterangan dokter</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <!-- Form -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-1">Form Pengajuan</h3>
                            <p class="text-sm text-slate-500 mb-6">Isi data dengan lengkap untuk mengajukan izin/cuti/sakit</p>

                            <form action="{{ route('leave.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Type Selection -->
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-slate-700 mb-3">Jenis Pengajuan</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        <!-- Izin -->
                                        <div class="type-card border-2 border-slate-200 rounded-xl p-4 text-center {{ old('type') == 'izin' ? 'selected' : '' }}" onclick="selectType('izin')" data-type="izin">
                                            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-clock text-amber-600 text-xl"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-700">Izin</p>
                                            <p class="text-xs text-slate-400">Sisa: {{ $izinRemaining }}</p>
                                        </div>
                                        <!-- Cuti -->
                                        <div class="type-card border-2 border-slate-200 rounded-xl p-4 text-center {{ old('type') == 'cuti' ? 'selected' : '' }}" onclick="selectType('cuti')" data-type="cuti">
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-umbrella-beach text-blue-600 text-xl"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-700">Cuti</p>
                                            <p class="text-xs text-slate-400">Sisa: {{ $cutiRemaining }}</p>
                                        </div>
                                        <!-- Sakit -->
                                        <div class="type-card border-2 border-slate-200 rounded-xl p-4 text-center {{ old('type') == 'sakit' ? 'selected' : '' }}" onclick="selectType('sakit')" data-type="sakit">
                                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <i class="fas fa-hospital text-red-600 text-xl"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-700">Sakit</p>
                                            <p class="text-xs text-slate-400">Wajib surat dokter</p>
                                        </div>
                                    </div>
                                    <input type="hidden" name="type" id="selectedType" value="{{ old('type') }}">
                                    @error('type')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Date Range -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Mulai</label>
                                        <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                            class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-sm outline-none">
                                        @error('start_date')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Selesai</label>
                                        <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                            class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-sm outline-none">
                                        @error('end_date')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Alasan -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Alasan</label>
                                    <textarea name="alasan" rows="4" required
                                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border-2 border-slate-200 text-sm outline-none resize-none"
                                        placeholder="Jelaskan alasan pengajuan izin/cuti/sakit Anda...">{{ old('alasan') }}</textarea>
                                    @error('alasan')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Upload Surat Dokter (hanya untuk sakit) -->
                                <div id="dokterUpload" class="mb-6 {{ old('type') !== 'sakit' ? 'hidden' : '' }}">
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Surat Keterangan Dokter</label>
                                    <div class="file-upload-area border-2 border-dashed border-slate-300 rounded-xl p-6 text-center">
                                        <i class="fas fa-file-medical-alt text-4xl text-slate-300 mb-3"></i>
                                        <p class="text-sm text-slate-500 mb-1">Upload foto surat keterangan dokter</p>
                                        <p class="text-xs text-slate-400 mb-3">Format: JPEG/PNG, Maks 2MB</p>
                                        <label class="inline-flex items-center px-4 py-2 bg-slate-100 rounded-lg cursor-pointer hover:bg-slate-200 transition">
                                            <i class="fas fa-upload mr-2 text-slate-600"></i>
                                            <span class="text-sm text-slate-600 font-medium">Pilih File</span>
                                            <input type="file" name="dokter_photo" id="dokterPhoto" accept="image/jpeg,image/png" class="hidden">
                                        </label>
                                        <p id="fileName" class="text-xs text-slate-500 mt-2"></p>
                                    </div>
                                    @error('dokter_photo')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="btn-submit w-full py-3.5 rounded-xl text-white font-semibold shadow-lg transition-all flex items-center justify-center space-x-2">
                                    <i class="fas fa-paper-plane"></i>
                                    <span>Ajukan</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Riwayat -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                            <h3 class="text-lg font-bold text-slate-800 mb-1">Riwayat Pengajuan</h3>
                            <p class="text-sm text-slate-500 mb-4">Data pengajuan terbaru</p>

                            @if($history->count() > 0)
                                <div class="space-y-3">
                                    @foreach($history as $item)
                                        <div class="p-4 border border-slate-100 rounded-xl hover:bg-slate-50 transition">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center space-x-2">
                                                    @if($item->type == 'izin')
                                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-medium rounded-full">Izin</span>
                                                    @elseif($item->type == 'cuti')
                                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">Cuti</span>
                                                    @else
                                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full">Sakit</span>
                                                    @endif
                                                    @if($item->status == 'pending')
                                                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs font-medium rounded-full">
                                                            <i class="fas fa-clock mr-1"></i>Pending
                                                        </span>
                                                    @elseif($item->status == 'disetujui')
                                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">
                                                            <i class="fas fa-check mr-1"></i>Disetujui
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                                            <i class="fas fa-times mr-1"></i>Ditolak
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-slate-400">{{ $item->created_at->format('d/m/Y') }}</span>
                                            </div>
                                            <p class="text-sm text-slate-600">{{ $item->start_date->format('d M') }} - {{ $item->end_date->format('d M Y') }}</p>
                                            <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $item->alasan }}</p>
                                            @if($item->catatan_admin)
                                                <p class="text-xs text-slate-500 mt-1 italic">Catatan admin: {{ $item->catatan_admin }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-4">
                                    {{ $history->links() }}
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <i class="fas fa-inbox text-4xl text-slate-300 mb-3"></i>
                                    <p class="text-sm text-slate-400">Belum ada riwayat pengajuan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Select type card
        function selectType(type) {
            document.querySelectorAll('.type-card').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.type-card[data-type="${type}"]`).classList.add('selected');
            document.getElementById('selectedType').value = type;

            // Tampilkan upload dokter hanya untuk sakit
            const dokterUpload = document.getElementById('dokterUpload');
            if (type === 'sakit') {
                dokterUpload.classList.remove('hidden');
            } else {
                dokterUpload.classList.add('hidden');
            }
        }

        // File name display
        document.getElementById('dokterPhoto').addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || '';
            document.getElementById('fileName').textContent = fileName ? 'File: ' + fileName : '';
        });

        // Mobile menu
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            // Simple toggle for demo - full sidebar toggle logic can be added
            alert('Sidebar tersedia di layar yang lebih besar.');
        });
    </script>
</body>
</html>