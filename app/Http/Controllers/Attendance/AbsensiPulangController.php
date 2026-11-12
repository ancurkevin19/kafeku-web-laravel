<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AbsensiPulangController extends Controller
{
    /**
     * Show the attendance pulang form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('livewire.absensi-pulang');
    }

    /**
     * Process attendance pulang.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function absenPulang(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'fotoSelfiePulang' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'radiusPilih2' => 'required',
        ]);

        // Get current employee
        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan.',
            ], 404);
        }

        // Check if already absen masuk today
        $today = now()->format('Y-m-d');
        $existingAbsen = Attendance::where('employee_id', $employee->id)
            ->where('tanggal', $today)
            ->where('status_masuk', '!=', null)
            ->first();

        if (!$existingAbsen) {
            return response()->json([
                'message' => 'Silakan absen masuk terlebih dahulu.',
            ], 400);
        }

        // Validate GPS
        $gpsValid = $this->validateGPS($request);

        if (!$gpsValid && $validated['radiusPilih2'] == 'tidak') {
            return response()->json([
                'message' => 'Anda berada di luar radius kantor. Tidak dapat absen pulang.',
            ], 403);
        }

        // Process and store photo
        $fotoPath = null;
        if ($request->hasFile('fotoSelfiePulang')) {
            $fotoPath = $request->file('fotoSelfiePulang')->store('attendance/foto_pulang', 'public');
        }

        // Update attendance record
        $existingAbsen->update([
            'jam_pulang' => now()->format('H:i:s'),
            'status_pulang' => $this->hitungStatusPulang(now()->format('H:i:s')),
            'lokasi_pulang_lat' => request('lat'),
            'lokasi_pulang_lng' => request('lng'),
            'jarak_pulang_meter' => request('jarak'),
            'foto_pulang' => $fotoPath,
            'status_verifikasi' => 'belum',
        ]);

        // Log activity
        $existingAbsen->logActivity('absen_pulang', 'Karyawan absen pulang');

        return response()->json([
            'message' => 'Absensi pulang berhasil!',
            'attendance_id' => $existingAbsen->id,
            'status' => $existingAbsen->status_pulang,
        ]);
    }

    /**
     * Hitung status pulang.
     *
     * @param string $jamPulang
     * @return string
     */
    protected function hitungStatusPulang($jamPulang)
    {
        $setting = auth()->user()->employee->company->attendance_settings->first();

        if (!$setting) {
            return 'tepat_waktu';
        }

        $jamPulangCarbon = \Carbon\Carbon::parse($jamPulang);
        $settingJamPulang = \Carbon\Carbon::parse($setting->jam_pulang);
        $selisihMenit = $jamPulangCarbon->diffInMinutes($settingJamPulang);

        if ($selisihMenit <= 0) {
            return 'tepat_waktu';
        }

        return 'awal_pulang';
    }

    /**
     * Validate GPS location for pulang.
     *
     * @param Request $request
     * @return bool
     */
    protected function validateGPS(Request $request)
    {
        $setting = auth()->user()->employee->company->attendance_settings->first();

        if (!$setting) {
            return true;
        }

        $lokasiKantorLat = $setting->office_lat;
        $lokasiKantorLng = $setting->office_lng;
        $radius = $setting->office_radius_meter;

        $lat = floatval($request->lat ?? 0);
        $lng = floatval($request->lng ?? 0);

        $jarak = hypot($lat - $lokasiKantorLat, $lng - $lokasiKantorLng) * 111320;

        return $jarak <= $radius;
    }
}