<div>
    <h3>Verifikasi Absensi</h3>
    
    <div class="row mb-3">
        <div class="col-12">
            <select class="form-select" wire:model="filterStatus">
                <option value="belum">Belum Diverifikasi</option>
                <option value="diverifikasi">Sudah Diverifikasi</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Karyawan</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Status Supervisor</th>
                    <th>Aksi HRD</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->employee->name }}</td>
                    <td>{{ $attendance->tanggal }}</td>
                    <td>{{ $attendance->jam_masuk }}</td>
                    <td>
                        <span class="badge bg-{{ $attendance->status_validasi == 'disetujui' ? 'primary' : 'secondary' }}">
                            {{ $attendance->status_validasi }}
                        </span>
                    </td>
                    <td>
                        <form wire:submit="verifikasiAbsensi({{ $attendance->id }})">
                            <select class="form-select form-select-sm" wire:model="actionVerifikasi">
                                <option value="diverifikasi">Verifikasi</option>
                                <option value="ditolak">Tolak Verifikasi</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary ms-2">Aksi</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (count($attendances) == 0)
        <div class="alert alert-info">
            Tidak ada absensi dengan status ini.
        </div>
    @endif
</div>

<script>
    window.Livewire.on('init', () => {
        loadAbsensiSemua();
        
        // Inisialisasi GPS status
        refreshGPSStatus();
        
        // Set interval refresh GPS tiap 5 detik
        window.gpsInterval = setInterval(refreshGPSStatus, 5000);
    });

    function loadAbsensiSemua() {
        // Fetch semua absensi yang sudah divalidasi supervisor
        // ...
    }
    
    // Fungsi refresh status GPS
    function refreshGPSStatus() {
        // Cek status GPS dan update tampilan
        // ...
    }
</script>