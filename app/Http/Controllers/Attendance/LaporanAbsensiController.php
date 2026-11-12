<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class LaporanAbsensiController extends Controller
{
    /**
     * Show the laporan absensi page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tanggalAwal = $request->query('tanggal_awal');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = Attendance::with(['employee', 'employee.user']);

        if ($tanggalAwal) {
            $query->where('tanggal', '>=', $tanggalAwal);
        }

        if ($tanggalAkhir) {
            $query->where('tanggal', '<=', $tanggalAkhir);
        }

        $laporan = $query->latest('tanggal')->paginate(50);

        return view('livewire.laporan-absensi', [
            'laporan' => $laporan,
            'filterTanggalAwal' => $tanggalAwal,
            'filterTanggalAkhir' => $tanggalAkhir,
        ]);
    }

    /**
     * Generate PDF laporan absensi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $laporan = Attendance::with(['employee', 'employee.user'])
            ->when($tanggalAwal, function ($q, $t) {
                $q->where('tanggal', '>=', $t);
            })
            ->when($tanggalAkhir, function ($q, $t) {
                $q->where('tanggal', '<=', $t);
            })
            ->get();

        // Generate PDF menggunakan DomPDF atau library lain
        // Untuk sementara, return response dengan data
        return response()->json([
            'message' => 'PDF generation feature - data siap',
            'laporan_count' => $laporan->count(),
            'tanggal_awal' => $tanggalAwal,
            'tanggal_akhir' => $tanggalAkhir,
        ]);
    }

    /**
     * Export ke Excel.
     *
     * @return \Illuminate\Http\DownloadableFile
     */
    public function exportExcel(Request $request)
    {
        $tanggalAwal = $request->query('tanggal_awal');
        $tanggalAkhir = $request->query('tanggal_akhir');

        $query = Attendance::with(['employee']);

        if ($tanggalAwal) {
            $query->where('tanggal', '>=', $tanggalAwal);
        }

        if ($tanggalAkhir) {
            $query->where('tanggal', '<=', $tanggalAkhir);
        }

        return (new class implements FromQuery, WithHeadings, WithMapping {
            public function query()
            {
                return Attendance::with(['employee'])
                    ->when($tanggalAwal, function ($q) use ($tanggalAwal) {
                        $q->where('tanggal', '>=', $tanggalAwal);
                    })
                    ->when($tanggalAkhir, function ($q) use ($tanggalAkhir) {
                        $q->where('tanggal', '<=', $tanggalAkhir);
                    })
                    ->latest('tanggal');
            }

            public function headings(): array
            {
                return [
                    'No',
                    'Karyawan',
                    'NIP/ID',
                    'Tanggal',
                    'Jam Masuk',
                    'Jam Pulang',
                    'Status Masuk',
                    'Status Pulang',
                    'Validasi',
                    'Verifikasi',
                ];
            }

            public function map($query): array
            {
                static $no = 0;
                $no++;

                return [
                    $no,
                    $query->employee->name ?? '-',
                    $query->employee->nomor_pegawai ?? '-',
                    Carbon::parse($query->tanggal)->format('d/m/Y'),
                    $query->jam_masuk ? $query->jam_masuk->format('H:i') : '-',
                    $query->jam_pulang ? $query->jam_pulang->format('H:i') : '-',
                    $query->status_masuk ?? '-',
                    $query->status_pulang ?? '-',
                    $query->status_validasi ?? '-',
                    $query->status_verifikasi ?? '-',
                ];
            }
        })->download('laporan-absensi-' . now()->format('Y_m_d') . '.xlsx');
    }
}