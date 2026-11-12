<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValidasiAbsensiController extends Controller
{
    /**
     * Show the validation page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');

        $query = Attendance::whereHas('employee', function ($q) {
            $q->where('status', true);
        });

        if ($statusFilter) {
            $query->where('status_validasi', $statusFilter);
        }

        $attendances = $query->with('employee')->latest('tanggal')->paginate(15);

        return view('livewire.validasi-absensi', [
            'attendances' => $attendances,
            'filterStatus' => $statusFilter,
        ]);
    }

    /**
     * Validate attendance (setujui/tolak).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validasiAbsensi(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // Hanya supervisor yang bisa validasi
        if (!auth()->user()->hasRole('supervisor')) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $validated = $request->validate([
            'actionValidasi' => 'required|in:disetujui,ditolak',
        ]);

        $attendance->update([
            'status_validasi' => $validated['actionValidasi'],
            'validated_by' => auth()->user()->id,
            'validated_at' => now(),
        ]);

        // Log activity
        $attendance->logActivity('validasi', 'Supervisor ' . $validated['actionValidasi'] . ' absensi ID ' . $attendance->id);

        return response()->json([
            'message' => 'Absensi ' . ucfirst($validated['actionValidasi']) . '!',
            'attendance_id' => $attendance->id,
            'status_validasi' => $attendance->status_validasi,
        ]);
    }

    /**
     * Show team attendance (read-only for supervisor).
     *
     * @return \Illuminate\Http\Response
     */
    public function teamAttendance()
    {
        $attendances = Attendance::whereHas('employee', function ($q) {
            $q->whereHas('role', function ($r) {
                $r->where('name', 'supervisor'); // atau crew dependendo setup
            });
        })->latest('tanggal')->take(50)->get();

        return view('livewire.absensi-tim', [
            'attendances' => $attendances,
        ]);
    }
}