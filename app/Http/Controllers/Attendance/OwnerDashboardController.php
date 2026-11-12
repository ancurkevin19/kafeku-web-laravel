<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OwnerDashboardController extends Controller
{
    /**
     * Show the owner dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Ambil data statistik untuk dashboard owner
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        // Total absensi hari ini
        $totalHariIni = Attendance::where('tanggal', today())->count();

        // Total absensi bulan ini
        $totalBulanIni = Attendance::whereBetween('tanggal', [$thisMonth, now()->endOfMonth()])->count();

        // Total terlambat bulan ini
        $totalTerlambatBulan = Attendance::whereBetween('tanggal', [$thisMonth, now()->endOfMonth()])
            ->where('status_masuk', 'terlambat')
            ->count();

        // Absensi per role
        $absensiPerRole = Attendance::with(['employee.role'])
            ->whereBetween('tanggal', [$thisMonth, now()->endOfMonth()])
            ->selectRaw('employee.role_id, count(*) as count')
            ->groupBy('employee.role_id')
            ->get()
            ->keyBy('role_id');

        // Data bulanan untuk grafik (last 6 months)
        $bulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $nextMonth = $month->endOfMonth();

            $bulanan[] = [
                'bulan' => $month->format('M Y'),
                'absensi_masuk': Attendance::whereBetween('tanggal', [$month, $nextMonth])
                    ->where('status_masuk', '!=', null)
                    ->count(),
                'absensi_pulang': Attendance::whereBetween('tanggal', [$month, $nextMonth])
                    ->where('status_pulang', '!=', null)
                    ->count(),
            ];
        }

        return view('livewire.owner-dashboard', [
            'totalHariIni' => $totalHariIni,
            'totalBulanIni' => $totalBulanIni,
            'totalTerlambatBulan' => $totalTerlambatBulan,
            'absensiPerRole' => $absensiPerRole,
            'bulanan' => $bulanan,
        ]);
    }

    /**
     * API endpoint untuk absensi per role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function absensiPerRoleAPI(Request $request)
    {
        $roleId = $request->query('role_id');

        $data = Attendance::when($roleId, function ($q) use ($roleId) {
            $q->whereHas('employee', function ($qq) use ($roleId) {
                $qq->where('role_id', $roleId);
            });
        })
            ->whereBetween('tanggal', [now()->subMonth()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('DATE(tanggal) as tanggal, count(*) as total')
            ->groupBy('DATE(tanggal)')
            ->orderBy('tanggal')
            ->get();

        return response()->json($data);
    }
}