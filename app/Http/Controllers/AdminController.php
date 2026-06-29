<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    public function employees(Request $request)
    {
        $query = User::where('role', 'employee')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();
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
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $employee->name = $validated['name'];
        $employee->email = $validated['email'];
        $employee->nip = $validated['nip'];
        $employee->phone = $validated['phone'] ?? null;
        $employee->address = $validated['address'] ?? null;
        $employee->tanggal_lahir = $validated['tanggal_lahir'] ?? null;

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if exists
            if ($employee->profile_photo && Storage::disk('public')->exists($employee->profile_photo)) {
                Storage::disk('public')->delete($employee->profile_photo);
            }
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $employee->profile_photo = $path;
        }

        if (!empty($validated['password'])) {
            $employee->password = Hash::make($validated['password']);
        }

        $employee->save();

        return redirect()->route('admin.employees')->with('success', 'Profil karyawan berhasil diperbarui!');
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
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $query = User::where('role', 'employee')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $employees = $query->get();
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

    /**
     * Export monthly recap to Excel.
     */
    public function exportRekap(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $employees = User::where('role', 'employee')->orderBy('name')->get();
        $namaBulan = \Carbon\Carbon::create()->month($bulan)->isoFormat('MMMM');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Rekap $namaBulan $tahun");

        // Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', "REKAP ABSENSI - $namaBulan $tahun");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // Header row
        $headers = ['No', 'NIP', 'Nama', 'Hadir', 'Izin', 'Sakit', 'Alpha'];
        $row = 3;
        foreach (range('A', 'G') as $i => $col) {
            $sheet->setCellValue($col . $row, $headers[$i]);
        }
        $headerStyle = $sheet->getStyle('A3:G3');
        $headerStyle->getFont()->setBold(true)->setSize(11);
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $headerStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6366f1');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        $sheet->getRowDimension('3')->setRowHeight(25);

        // Borders for header
        $headerBorders = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ];
        $sheet->getStyle('A3:G3')->applyFromArray($headerBorders);

        // Data
        $row = 4;
        $no = 1;
        foreach ($employees as $employee) {
            $totalHadir = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'hadir')->count();
            $totalIzin = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'izin')->count();
            $totalSakit = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'sakit')->count();
            $totalAlpha = Attendance::where('user_id', $employee->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('status', 'alpha')->count();

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $employee->nip ?? '-');
            $sheet->setCellValue('C' . $row, $employee->name);
            $sheet->setCellValue('D' . $row, $totalHadir);
            $sheet->setCellValue('E' . $row, $totalIzin);
            $sheet->setCellValue('F' . $row, $totalSakit);
            $sheet->setCellValue('G' . $row, $totalAlpha);

            $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($headerBorders);
            $sheet->getStyle('A' . $row . ':G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Alternate row color
            if ($no % 2 == 0) {
                $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
            }

            $no++;
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "rekap_absensi_{$namaBulan}_{$tahun}.xlsx";

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    // ==================== MANAJEMEN PERIZINAN ====================

    /**
     * Display all leave requests.
     */
    public function leaveRequests(Request $request)
    {
        $query = LeaveRequest::with(['user', 'approver'])->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by employee name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $leaveRequests = $query->paginate(20);

        // Stats
        $totalPending = LeaveRequest::where('status', 'pending')->count();

        return view('admin.leaves.index', compact('leaveRequests', 'totalPending'));
    }

    /**
     * Approve a leave request.
     */
    public function approveLeave($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $leave->status = 'disetujui';
        $leave->approved_at = now();
        $leave->approved_by = auth()->id();
        $leave->save();

        return redirect()->route('admin.leaves')->with('success', 'Pengajuan ' . $leave->type . ' dari ' . $leave->user->name . ' berhasil disetujui!');
    }

    /**
     * Reject a leave request with keterangan.
     */
    public function rejectLeave(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);

        $request->validate([
            'catatan_admin' => 'required|string|min:3',
        ]);

        $leave->status = 'ditolak';
        $leave->catatan_admin = $request->catatan_admin;
        $leave->approved_at = now();
        $leave->approved_by = auth()->id();
        $leave->save();

        return redirect()->route('admin.leaves')->with('success', 'Pengajuan ' . $leave->type . ' dari ' . $leave->user->name . ' ditolak.');
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