<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
}