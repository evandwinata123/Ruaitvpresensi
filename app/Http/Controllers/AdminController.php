<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function index()
    {
        $totalKaryawan = User::where('role', 'employee')->count();
        $totalPresensiHariIni = Attendance::where('tanggal', now()->toDateString())->count();
        $totalIzinHariIni = Attendance::where('tanggal', now()->toDateString())->where('status', 'izin')->count();
        $totalSakitHariIni = Attendance::where('tanggal', now()->toDateString())->where('status', 'sakit')->count();

        return view('admin.dashboard', compact('totalKaryawan', 'totalPresensiHariIni', 'totalIzinHariIni', 'totalSakitHariIni'));
    }

    // ==================== MANAJEMEN KARYAWAN ====================

    /**
     * Display list of employees.
     */
    public function employees()
    {
        $employees = User::where('role', 'employee')->orderBy('name')->get();
        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show form to create a new employee.
     */
    public function createEmployee()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a new employee.
     */
    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'nip' => 'required|string|max:50|unique:users',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'employee';

        User::create($validated);

        return redirect()->route('admin.employees')->with('success', 'Karyawan berhasil ditambahkan!');
    }

    /**
     * Show form to edit an employee.
     */
    public function editEmployee($id)
    {
        $employee = User::findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update an employee.
     */
    public function updateEmployee(Request $request, $id)
    {
        $employee = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($employee->id)],
            'nip' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($employee->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $employee->name = $validated['name'];
        $employee->email = $validated['email'];
        $employee->nip = $validated['nip'];
        $employee->phone = $validated['phone'] ?? null;
        $employee->address = $validated['address'] ?? null;
        $employee->tanggal_lahir = $validated['tanggal_lahir'] ?? null;

        if (!empty($validated['password'])) {
            $employee->password = Hash::make($validated['password']);
        }

        $employee->save();

        return redirect()->route('admin.employees')->with('success', 'Karyawan berhasil diperbarui!');
    }

    /**
     * Delete an employee.
     */
    public function deleteEmployee($id)
    {
        $employee = User::findOrFail($id);
        // Delete related attendance records
        Attendance::where('user_id', $employee->id)->delete();
        $employee->delete();

        return redirect()->route('admin.employees')->with('success', 'Karyawan berhasil dihapus!');
    }

    // ==================== REVIEW PRESENSI ====================

    /**
     * Display attendance records pending review.
     */
    public function reviewPresensi()
    {
        $attendances = Attendance::with('user')
            ->whereDate('tanggal', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.review.index', compact('attendances'));
    }

    /**
     * Approve an attendance record.
     */
    public function approveAttendance($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->status = 'hadir';
        $attendance->save();

        return redirect()->route('admin.review')->with('success', 'Presensi berhasil disetujui!');
    }

    /**
     * Reject an attendance record.
     */
    public function rejectAttendance(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->status = 'alpha';
        $attendance->keterangan = $request->input('keterangan', 'Ditolak oleh admin');
        $attendance->save();

        return redirect()->route('admin.review')->with('success', 'Presensi ditolak!');
    }

    // ==================== REKAP ABSENSI ====================

    /**
     * Display monthly attendance recap.
     */
    public function rekapAbsensi(Request $request)
    {
        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $employees = User::where('role', 'employee')->orderBy('name')->get();
        $rekap = [];

        foreach ($employees as $employee) {
            $totalHadir = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'hadir')
                ->count();

            $totalIzin = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'izin')
                ->count();

            $totalSakit = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'sakit')
                ->count();

            $totalAlpha = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'alpha')
                ->count();

            $rekap[] = [
                'employee' => $employee,
                'totalHadir' => $totalHadir,
                'totalIzin' => $totalIzin,
                'totalSakit' => $totalSakit,
                'totalAlpha' => $totalAlpha,
            ];
        }

        return view('admin.rekap.index', compact('rekap', 'bulan', 'tahun'));
    }

    // ==================== LAPORAN DETAIL ====================

    /**
     * Display detailed attendance report.
     */
    public function laporanDetail(Request $request)
    {
        $query = Attendance::with('user')->orderBy('tanggal', 'desc');

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        // Filter by date range
        if ($request->filled('dari')) {
            $query->whereDate('tanggal', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal', '<=', $request->sampai);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->paginate(20);
        $employees = User::where('role', 'employee')->orderBy('name')->get();

        return view('admin.laporan.index', compact('attendances', 'employees'));
    }
}