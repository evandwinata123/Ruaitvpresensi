<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AttendanceController extends Controller
{
    /**
     * Check-in: presensi masuk dengan selfie dan lokasi.
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'photo' => 'required|string', // base64 image
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $today = now()->toDateString();

        // Cek apakah sudah check-in hari ini
        $existing = Attendance::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($existing && $existing->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi masuk hari ini.',
            ], 400);
        }

        // Simpan foto selfie
        $photoData = $request->input('photo');
        $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
        $photoData = str_replace('data:image/png;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoName = 'checkin_' . $user->id . '_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put('photos/' . $photoName, base64_decode($photoData));

        $attendance = Attendance::updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => $today],
            [
                'check_in_time' => now(),
                'check_in_photo' => $photoName,
                'check_in_latitude' => $request->latitude,
                'check_in_longitude' => $request->longitude,
                'status' => 'hadir',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Presensi masuk berhasil!',
            'data' => [
                'time' => $attendance->check_in_time->format('H:i'),
                'photo' => asset('storage/photos/' . $photoName),
            ]
        ]);
    }

    /**
     * Check-out: presensi pulang dengan selfie dan lokasi.
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'photo' => 'required|string', // base64 image
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan presensi masuk hari ini.',
            ], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan presensi pulang hari ini.',
            ], 400);
        }

        // Simpan foto selfie
        $photoData = $request->input('photo');
        $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
        $photoData = str_replace('data:image/png;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoName = 'checkout_' . $user->id . '_' . now()->format('Ymd_His') . '.jpg';
        Storage::disk('public')->put('photos/' . $photoName, base64_decode($photoData));

        $attendance->update([
            'check_out_time' => now(),
            'check_out_photo' => $photoName,
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi pulang berhasil!',
            'data' => [
                'time' => $attendance->check_out_time->format('H:i'),
                'photo' => asset('storage/photos/' . $photoName),
            ]
        ]);
    }

    /**
     * Get today's attendance status for the current user.
     */
    public function status()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'checked_in' => $attendance && $attendance->check_in_time ? true : false,
                'checked_out' => $attendance && $attendance->check_out_time ? true : false,
                'check_in_time' => $attendance?->check_in_time?->format('H:i'),
                'check_out_time' => $attendance?->check_out_time?->format('H:i'),
                'check_in_photo' => $attendance?->check_in_photo ? asset('storage/photos/' . $attendance->check_in_photo) : null,
                'check_out_photo' => $attendance?->check_out_photo ? asset('storage/photos/' . $attendance->check_out_photo) : null,
                'latitude' => $attendance?->check_in_latitude,
                'longitude' => $attendance?->check_in_longitude,
            ]
        ]);
    }

    /**
     * Get attendance history for the current user.
     */
    public function history()
    {
        $user = Auth::user();

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal->format('Y-m-d'),
                    'hari' => $item->tanggal->isoFormat('dddd'),
                    'check_in' => $item->check_in_time?->format('H:i'),
                    'check_out' => $item->check_out_time?->format('H:i'),
                    'status' => $item->status,
                    'keterangan' => $item->keterangan,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * Display monthly attendance history page for employee.
     */
    public function historyPage($bulan = null, $tahun = null)
    {
        $user = Auth::user();

        // Default to current month/year
        if (!$bulan || $bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }
        if (!$tahun || $tahun < 2000) {
            $tahun = now()->year;
        }

        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$bulan];

        // Get attendances for the selected month
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Calculate summary
        $totalHari = $attendances->count();
        $hadir = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $izinSakit = $attendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $alpha = $attendances->where('status', 'alpha')->count();
        $persenHadir = $totalHari > 0 ? round(($hadir / $totalHari) * 100) : 0;

        $summary = [
            'total_hari' => $totalHari,
            'hadir' => $hadir,
            'izin_sakit' => $izinSakit,
            'alpha' => $alpha,
            'persen_hadir' => $persenHadir,
        ];

        // Monthly stats for the grid (all months in selected year)
        $monthlyStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthAttendances = Attendance::where('user_id', $user->id)
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $m)
                ->get();

            $total = $monthAttendances->count();
            $hadirCount = $monthAttendances->whereIn('status', ['hadir', 'terlambat'])->count();
            $monthlyStats[$m] = [
                'hadir' => $hadirCount,
                'total' => $total,
                'persen' => $total > 0 ? round(($hadirCount / $total) * 100) : 0,
            ];
        }

        return view('attendance.history', compact(
            'attendances',
            'bulan',
            'tahun',
            'bulanNama',
            'summary',
            'monthlyStats'
        ));
    }

    /**
     * Export monthly attendance to Excel.
     */
    public function exportExcel($bulan, $tahun)
    {
        $user = Auth::user();

        if (!$bulan || $bulan < 1 || $bulan > 12) {
            $bulan = now()->month;
        }
        if (!$tahun || $tahun < 2000) {
            $tahun = now()->year;
        }

        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ][$bulan];

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Presensi $bulanNama $tahun");

        // Title
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', "LAPORAN PRESENSI - $bulanNama $tahun");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Employee info
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', "Nama: {$user->name} | NIP: {$user->nip}");
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setSize(11);

        // Summary
        $totalHari = $attendances->count();
        $hadir = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $izinSakit = $attendances->whereIn('status', ['izin', 'sakit', 'cuti'])->count();
        $alpha = $attendances->where('status', 'alpha')->count();

        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', "Hadir: $hadir | Izin/Sakit: $izinSakit | Alpha: $alpha | Total: $totalHari hari");
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);

        // Header row
        $headers = ['No', 'Tanggal', 'Hari', 'Jam Masuk', 'Jam Pulang', 'Status'];
        $headerRow = 5;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($col . $headerRow)->getFill()->getStartColor()->setARGB('FF667EEA');
            $sheet->getStyle($col . $headerRow)->getFont()->getColor()->setARGB('FFFFFFFF');
            $col++;
        }

        // Data rows
        $row = 6;
        $no = 1;
        foreach ($attendances as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->tanggal)->isoFormat('D MMMM YYYY'));
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($item->tanggal)->isoFormat('dddd'));
            $sheet->setCellValue('D' . $row, $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : '-');
            $sheet->setCellValue('E' . $row, $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : '-');
            $sheet->setCellValue('F' . $row, ucfirst($item->status));

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Color status
            $statusColor = match($item->status) {
                'hadir' => 'FF22C55E',
                'terlambat' => 'FFF97316',
                'izin' => 'FF3B82F6',
                'sakit' => 'FFEF4444',
                'alpha' => 'FF9CA3AF',
                'cuti' => 'FF6366F1',
                default => 'FF9CA3AF'
            };
            $sheet->getStyle('F' . $row)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle('F' . $row)->getFill()->getStartColor()->setARGB($statusColor);
            $sheet->getStyle('F' . $row)->getFont()->getColor()->setARGB('FFFFFFFF');

            $row++;
            $no++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $lastRow = $row - 1;
        $sheet->getStyle("A5:F$lastRow")->applyFromArray($styleArray);

        // Generate file
        $writer = new Xlsx($spreadsheet);
        $fileName = "Presensi_{$bulanNama}_{$tahun}_{$user->name}.xlsx";
        $filePath = storage_path("app/public/exports/$fileName");

        // Ensure directory exists
        $dir = storage_path('app/public/exports');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer->save($filePath);

        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
}
