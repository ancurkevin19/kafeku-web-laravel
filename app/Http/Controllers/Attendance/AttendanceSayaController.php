<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AttendanceSayaController extends Controller
{
    /**
     * Show the attendance saya (own history) page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Filter by status jika ada
        $statusFilter = $request->query('status');

        $attendances = Attendance::where('employee_id', auth()->user()->employee->id);

        if ($statusFilter) {
            $attendances->where('status_masuk', $statusFilter);
        }

        $attendances = $attendances->latest('tanggal')->paginate(10);

        return view('livewire.absensi-saya', [
            'attendances' => $attendances,
            'filterStatus' => $statusFilter,
        ]);
    }

    /**
     * Get attendance detail by ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        $attendance = Attendance::with('employee')->findOrFail($id);

        return view('livewire.absensi-detail', [
            'attendance' => $attendance,
        ]);
    }
}