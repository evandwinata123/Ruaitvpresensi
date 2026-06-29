<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    /**
     * Show the leave application page.
     */
    public function index()
    {
        $user = Auth::user();

        // Hitung sisa izin bulan ini (reset tiap bulan)
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $izinUsedThisMonth = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'izin')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();
        $izinRemaining = max(0, ($user->izin_quota ?? 5) - $izinUsedThisMonth);

        // Hitung sisa cuti tahun ini
        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();
        $cutiUsedThisYear = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'cuti')
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();
        $cutiRemaining = max(0, ($user->cuti_quota ?? 12) - $cutiUsedThisYear);

        // Riwayat pengajuan
        $history = LeaveRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('leave.index', compact(
            'izinRemaining',
            'cutiRemaining',
            'history',
            'izinUsedThisMonth',
            'cutiUsedThisYear'
        ));
    }

    /**
     * Store a new leave request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'type' => 'required|in:izin,cuti,sakit',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'alasan' => 'required|string|min:10',
        ];

        $messages = [
            'start_date.after_or_equal' => 'Tanggal mulai harus hari ini atau setelahnya.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
        ];

        // Jika sakit, wajib upload foto surat dokter
        if ($request->type === 'sakit') {
            $rules['dokter_photo'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            $messages['dokter_photo.required'] = 'Foto surat keterangan dokter wajib diupload.';
            $messages['dokter_photo.image'] = 'File harus berupa gambar.';
            $messages['dokter_photo.max'] = 'Ukuran foto maksimal 2MB.';
        }

        $validated = $request->validate($rules, $messages);

        // Cek kuota izin (5x per bulan)
        if ($request->type === 'izin') {
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            $izinUsedThisMonth = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'izin')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereIn('status', ['pending', 'disetujui'])
                ->count();

            if ($izinUsedThisMonth >= ($user->izin_quota ?? 5)) {
                return back()->withErrors(['type' => 'Kuota izin bulan ini sudah habis (5x).'])->withInput();
            }
        }

        // Cek kuota cuti (12x per tahun)
        if ($request->type === 'cuti') {
            $yearStart = Carbon::now()->startOfYear();
            $yearEnd = Carbon::now()->endOfYear();
            $cutiUsedThisYear = LeaveRequest::where('user_id', $user->id)
                ->where('type', 'cuti')
                ->whereBetween('created_at', [$yearStart, $yearEnd])
                ->whereIn('status', ['pending', 'disetujui'])
                ->count();

            if ($cutiUsedThisYear >= ($user->cuti_quota ?? 12)) {
                return back()->withErrors(['type' => 'Kuota cuti tahun ini sudah habis.'])->withInput();
            }
        }

        // Simpan foto surat dokter jika ada
        $dokterPhotoName = null;
        if ($request->hasFile('dokter_photo')) {
            $dokterPhotoName = 'dokter_' . $user->id . '_' . time() . '.' . $request->file('dokter_photo')->extension();
            $request->file('dokter_photo')->storeAs('public/dokter', $dokterPhotoName);
        }

        LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'alasan' => $request->alasan,
            'dokter_photo' => $dokterPhotoName,
            'status' => 'pending',
        ]);

        return redirect()->route('leave.index')->with('success', 'Pengajuan ' . $request->type . ' berhasil dikirim, menunggu persetujuan admin.');
    }
}