<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanAbsensiSeeder extends Seeder implements FromQuery, WithHeadings, WithMapping
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder ini akan digunakan untuk generate export laporan
        // Data sudah ada dari AttendanceSeeder dan migration
    }

    /**
     * Query data untuk laporan.
     */
    public function query()
    {
        return Attendance::with(['employee', 'employee.user'])
            ->when(request('tanggal'), function ($query, $tanggal) {
                return $query->where('tanggal', $tanggal);
            })
            ->when(request('status'), function ($query, $status) {
                return $query->where('status_masuk', $status);
            })
            ->latest('tanggal');
    }

    /**
     * Headings for Excel export.
     */
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
            'Jarak (m)',
            'Validasi',
            'Verifikasi',
        ];
    }

    /**
     * Map query results to Excel rows.
     */
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
            $query->jarak_masuk_meter ?? 0,
            $query->status_validasi ?? '-',
            $query->status_verifikasi ?? '-',
        ];
    }
}