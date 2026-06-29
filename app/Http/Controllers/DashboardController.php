<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function employee()
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $yearStart = Carbon::now()->startOfYear();
        $yearEnd = Carbon::now()->endOfYear();

        // Total Hadir bulan ini
        $totalHadir = Attendance::where('user_id', $user->id)
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->where('status', 'hadir')
            ->count();

        // Total Izin bulan ini (disetujui + pending)
        $totalIzin = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'izin')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();

        // Total Sakit bulan ini (disetujui + pending)
        $totalSakit = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'sakit')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();

        // Sisa Cuti tahun ini
        $cutiUsed = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'cuti')
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();
        $sisaCuti = max(0, ($user->cuti_quota ?? 12) - $cutiUsed);

        // Sisa Izin bulan ini
        $izinUsed = LeaveRequest::where('user_id', $user->id)
            ->where('type', 'izin')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['pending', 'disetujui'])
            ->count();
        $sisaIzin = max(0, ($user->izin_quota ?? 5) - $izinUsed);

        return view('dashboard.employee', compact(
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'sisaCuti',
            'sisaIzin'
        ));
    }
}