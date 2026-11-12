<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AttendanceCorrection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KoreksiAbsensiController extends Controller
{
    /**
     * Show the correction form.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $attendance = Attendance::with('employee')->findOrFail($id);

        // Hanya HRD yang bisa koreksi
        if (!auth()->user()->hasRole('hrd')) {
            abort(403);
        }

        // Ambil nilai lama untuk field yang dipilih
        $nilamaLama = null;
        if (request('field') == 'jam_masuk') {
            $nilamaLama = $attendance->jam_masuk;
        } elseif (request('field') == 'jam_pulang') {
            $nilamaLama = $attendance->jam_pulang;
        } elseif (request('field') == 'keterangan') {
            $nilamaLama = $attendance->keterangan;
        }

        return view('livewire.koreksi-absensi', [
            'attendance' => $attendance,
            'nilamaLama' => $nilamaLama,
            'field' => request('field'),
        ]);
    }

    /**
     * Save attendance correction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function simpanKoreksi(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hanya HRD yang bisa koreksi
        if (!auth()->user()->hasRole('hrd')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'fieldKoreksi' => 'required',
            'nilaiBaru' => 'required',
            'alasanKoreksi' => 'required',
        ]);

        // Buat record koreksi
        AttendanceCorrection::create([
            'attendance_id' => $attendance->id,
            'field' => $validated['fieldKoreksi'],
            'nilai_lama' => request('nilaiLama'),
            'nilai_baru' => $validated['nilaiBaru'],
            'alasan' => $validated['alasanKoreksi'],
            'corrected_by' => auth()->user()->id,
        ]);

        // Update attendance field
        $attendance->update([
            $validated['fieldKoreksi'] => $validated['nilaiBaru'],
            'is_corrected' => true,
            'corrected_by' => auth()->user()->id,
            'corrected_at' => now(),
        ]);

        // Log activity
        $attendance->logActivity('koreksi', 'HRD memperbaiki field ' . $validated['fieldKoreksi']);

        return response()->json([
            'message' => 'Koreksi berhasil disimpan!',
            'attendance_id' => $attendance->id,
            'field' => $validated['fieldKoreksi'],
            'nilaiBaru' => $validated['nilaiBaru'],
        ]);
    }
}