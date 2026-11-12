<?php

namespace App\Http\Controllers\Attendance\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceApi extends Controller
{
    /**
     * Get attendance settings.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings()
    {
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'office_lat' => 0,
                'office_lng' => 0,
                'office_radius_meter' => 250,
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '17:00:00',
                'toleransi_terlambat_menit' => 15,
                'wajib_selfie' => true,
                'wajib_gps' => true,
            ]);
        }

        return response()->json($setting->toArray());
    }

    /**
     * Update attendance settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'office_lat' => 'nullable|numeric',
            'office_lng' => 'nullable|numeric',
            'office_radius_meter' => 'nullable|integer',
            'jam_masuk' => 'nullable|date_format:H:i:s',
            'jam_pulang' => 'nullable|date_format:H:i:s',
            'toleransi_terlambat_menit' => 'nullable|integer',
            'wajib_selfie' => 'nullable|boolean',
            'wajib_gps' => 'nullable|boolean',
        ]);

        $setting = AttendanceSetting::firstOrCreate(['id' => 1]);

        $setting->update($validated);

        return response()->json([
            'message' => 'Setting absensi berhasil diupdate!',
            'setting' => $setting->toArray(),
        ]);
    }

    /**
     * Validate GPS location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateGPS(Request $request)
    {
        $setting = AttendanceSetting::first();

        if (!$setting) {
            return response()->json([
                'valid' => true,
                'message' => 'Setting tidak ditemukan, GPS diizinkan.'
            ]);
        }

        $lat = floatval($request->input('lat', 0));
        $lng = floatval($request->input('lng', 0));

        // Hitung jarak ke kantor (approximate)
        $lokasiKantorLat = $setting->office_lat;
        $lokasiKantorLng = $setting->office_lng;
        $radius = $setting->office_radius_meter;

        // Rumus jarak approximated
        $jarak = hypot($lat - $lokasiKantorLat, $lng - $lokasiKantorLng) * 111320;

        $valid = $jarak <= $radius;

        return response()->json([
            'valid' => $valid,
            'jarak_meter' => round($jarak),
            'radius_meter' => $radius,
            'message' => $valid ? 'Di dalam radius kantor' : 'Di luar radius kantor, jarak ' . round($jarak) . ' meter'
        ]);
    }

    /**
     * Upload foto selfie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFoto(Request $request)
    {
        $validated = $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('foto')->store('attendance/foto_temp', 'public');

        return response()->json([
            'message' => 'Foto berhasil diupload!',
            'foto_path' => $path,
        ]);
    }

    /**
     * Get activity log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityLog(Request $request)
    {
        $limit = $request->integer('limit', 50);

        $log = \Log::channel('activity')->logs()->take($limit);

        return response()->json([
            'logs' => array_reverse($log),
        ]);
    }
}