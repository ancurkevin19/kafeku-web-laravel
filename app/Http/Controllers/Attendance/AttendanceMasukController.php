<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceMasukController extends Controller
{
    /**
     * Show the attendance masuk form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('livewire.absensi-masuk');
    }

    /**
     * Process attendance masuk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function absenMasuk(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'fotoSelfie' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'radiusPilih' => 'required',
        ]);

        // Get current employee
        $employee = auth()->user()->employee;

        if (!$employee) {
            return response()->json([
                'message' => 'Karyawan tidak ditemukan.',
            ], 404);
        }

        // Validate GPS (implementasi sederhana)
        $gpsValid = $this->validateGPS($request);

        if (!$gpsValid && $validated['radiusPilih'] == 'tidak') {
            return response()->json([
                'message' => 'Anda berada di luar radius kantor. Silakan mendekatkan diri ke kantor.',
            ], 403);
        }

        // Process and store photo
        $fotoPath = null;
        if ($request->hasFile('fotoSelfie')) {
            $fotoPath = $request->file('fotoSelfie')->store('attendance/foto_masuk', 'public');
        }

        // Create attendance record
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'tanggal' => now()->format('Y-m-d'),
            'jam_masuk' => now()->format('H:i:s'),
            'status_masuk' => $this->hitungStatusMasuk(now()->format('H:i:s')),
            'lokasi_masuk_lat' => request('lat'), // Dari GPS
            'lokasi_masuk_lng' => request('lng'), // Dari GPS
            'jarak_masuk_meter' => request('jarak'), // Dari GPS
            'foto_masuk' => $fotoPath,
            'keterangan' => $request->keterangan,
            'status_validasi' => 'pending',
        ]);

        // Log activity
        $attendance->logActivity('absen_masuk', 'Karyawan absen masuk');

        return response()->json([
            'message' => 'Absensi masuk berhasil!',
            'attendance_id' => $attendance->id,
            'status' => $attendance->status_masuk,
        ]);
    }

    /**
     * Hitung status masuk (tepat waktu/terlambat).
     *
     * @param string $jamMasuk
     * @return string
     */
    protected function hitungStatusMasuk($jamMasuk)
    {
        $setting = auth()->user()->employee->company->attendance_settings->first();

        if (!$setting) {
            return 'tepat_waktu';
        }

        $jamMasukCarbon = \Carbon\Carbon::parse($jamMasuk);
        $settingJamMasuk = \Carbon\Carbon::parse($setting->jam_masuk);
        $selisihMenit = $jamMasukCarbon->diffInMinutes($settingJamMasuk);

        if ($selisihMenit <= $setting->toleransi_terlambat_menit) {
            return 'tepat_waktu';
        }

        return 'terlambat';
    }

    /**
     * Validate GPS location.
     *
     * @param Request $request
     * @return bool
     */
    protected function validateGPS(Request $request)
    {
        $setting = auth()->user()->employee->company->attendance_settings->first();

        if (!$setting) {
            return true; // Jika setting tidak ada, izinkan
        }

        $lokasiKantorLat = $setting->office_lat;
        $lokasiKantorLng = $setting->office_lng;
        $radius = $setting->office_radius_meter;

        // Dapatkan koordinat dari request
        $lat = floatval($request->lat ?? 0);
        $lng = floatval($request->lng ?? 0);

        // Hitung jarak (rumus sederhana - Haversine tanpa complete)
        // Untuk produksi, gunakan library geo distance
        $jarak = hypot($lat - $lokasiKantorLat, $lng - $lokasiKantorLng) * 111320; // Approximate

        return $jarak <= $radius;
    }
}