<div>
    <h3>Validasi Absensi Tim</h3>
    
    <div class="row mb-3">
        <div class="col-12">
            <select class="form-select" wire:model="filterStatus">
                <option value="pending">Pending (Belum Divalidasi)</option>
                <option value="disetujui">Disetujui</option>
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
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->employee->name }}</td>
                    <td>{{ $attendance->tanggal }}</td>
                    <td>{{ $attendance->jam_masuk }}</td>
                    <td>
                        @if($attendance->foto_masuk)
                        <img src="{{ asset('storage/' . $attendance->foto_masuk) }}" width="50" height="50" class="rounded-circle">
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $attendance->status_validasi == 'disetujui' ? 'success' : 'danger' }}">
                            {{ ucfirst($attendance->status_validasi) }}
                        </span>
                    </td>
                    <td>
                        <form wire:submit="validasiAbsensi({{ $attendance->id }})">
                            <select class="form-select form-select-sm" wire:model="actionValidasi">
                                <option value="disetujui">Setujui</option>
                                <option value="ditolak">Tolak</option>
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
        loadAbsensiTim();
        
        // Inisialisasi GPS status
        refreshGPSStatus();
        
        // Set interval refresh GPS tiap 5 detik
        window.gpsInterval = setInterval(refreshGPSStatus, 5000);
    });

    function loadAbsensiTim() {
        // Fetch attendance data yang perlu divalidasi
        // ...
    }
    
    // Fungsi refresh status GPS
    function refreshGPSStatus() {
        // Cek status GPS dan update tampilan
        // ...
    }
</script>