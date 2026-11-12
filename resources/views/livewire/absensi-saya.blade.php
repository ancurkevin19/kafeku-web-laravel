<div>
    <h3>Absensi Saya</h3>
    
    <div class="row mb-3">
        <div class="col-12">
            <select class="form-select" wire:model="filterStatus">
                <option value="semua">Semua Status</option>
                <option value="tepat_waktu">Tepat Waktu</option>
                <option value="terlambat">Terlambat</option>
                <option value="belum_absen">Belum Absen</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->tanggal }}</td>
                    <td>{{ $attendance->jam_masuk ?? '-' }}</td>
                    <td>{{ $attendance->jam_pulang ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $attendance->status_masuk == 'tepat_waktu' ? 'success' : 'warning' }}">
                            {{ $attendance->status_masuk }}
                        </span>
                    </td>
                    <td>
                        @can('attendance.view.all')
                        <a href="{{ route('absensi.detail', $attendance->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (count($attendances) == 0)
        <div class="alert alert-info">
            Belum ada data absensi untuk periode ini.
        </div>
    @endif
</div>

<script>
    window.Livewire.on('init', () => {
        // Load attendance data saat component diinisialisasi
        loadAttendance();
        
        // Inisialisasi GPS status
        refreshGPSStatus();
        
        // Set interval refresh GPS tiap 5 detik
        window.gpsInterval = setInterval(refreshGPSStatus, 5000);
    });

    function loadAttendance() {
        // Fetch attendance data dari API
        // ...
    }
    
    // Fungsi refresh status GPS
    function refreshGPSStatus() {
        // Cek status GPS dan update tampilan
        // ...
    }
</script>