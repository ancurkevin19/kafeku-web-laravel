<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifikasiAbsensiController extends Controller
{
    /**
     * Show the verification page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');

        $query = Attendance::whereHas('employee', function ($q) {
            $q->whereHas('role', function ($r) {
                $r->where('name', 'hrd');
            });
        });

        if ($statusFilter) {
            $query->where('status_verifikasi', $statusFilter);
        }

        $attendances = $query->with('employee')->latest('tanggal')->paginate(15);

        return view('livewire.verifikasi-absensi', [
            'attendances' => $attendances,
            'filterStatus' => $statusFilter,
        ]);
    }

    /**
     * Verify attendance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function verifikasiAbsensi(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hanya HRD yang bisa verifikasi
        if (!auth()->user()->hasRole('hrd')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'actionVerifikasi' => 'required|diverifikasi|ditolak',
        ]);

        $attendance->update([
            'status_verifikasi' => $validated['actionVerifikasi'],
            'verification_by' => auth()->user()->id,
            'verification_at' => now(),
        ]);

        // Log activity
        $attendance->logActivity('verifikasi', 'HRD ' . $validated['actionVerifikasi'] . ' absensi ID ' . $attendance->id);

        return response()->json([
            'message' => 'Absensi ' . ucfirst($validated['actionVerifikasi']) . '!',
            'attendance_id' => $attendance->id,
            'status_verifikasi' => $attendance->status_verifikasi,
        ]);
    }

    /**
     * Show all attendance (for HRD overview).
     *
     * @return \Illuminate\Http\Response
     */
    public function allAttendance()
    {
        $attendances = Attendance::with(['employee', 'employee.role'])
            ->latest('tanggal')
            ->paginate(50);

        return view('livewire.absensi-semua', [
            'attendances' => $attendances,
        ]);
    }
}