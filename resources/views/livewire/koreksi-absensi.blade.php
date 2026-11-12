<div>
    <h3>Koreksi Absensi HRD</h3>
    
    <form wire:submit="simpanKoreksi">
        <input type="hidden" wire:model="attendanceId" value="{{ old('attendanceId') }}">
        
        <div class="mb-3">
            <label>Pilih Field Koreksi</label>
            <select class="form-select" wire:model="fieldKoreksi">
                <option value="jam_masuk">Jam Masuk</option>
                <option value="jam_pulang">Jam Pulang</option>
                <option value="lokasi_masuk_lat">Latitude Masuk</option>
                <option value="lokasi_masuk_lng">Longitude Masuk</option>
                <option value="keterangan">Keterangan</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Nilai Lama</label>
            <input type="text" class="form-control" wire:model="nilaiLama" readonly>
        </div>

        <div class="mb-3">
            <label>Nilai Baru</label>
            <input type="text" class="form-control" wire:model="nilaiBaru">
        </div>

        <div class="mb-3">
            <label>Alasan Koreksi</label>
            <textarea class="form-control" wire:model="alasanKoreksi" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-2"></i>
            Simpan Koreksi
        </button>
    </form>
</div>

<script>
    window.Livewire.on('init', () => {
        loadDataKoreksi();
        
        // Inisialisasi GPS status
        refreshGPSStatus();
        
        // Set interval refresh GPS tiap 5 detik
        window.gpsInterval = setInterval(refreshGPSStatus, 5000);
    });

    function loadDataKoreksi() {
        // Load data attendance dan nilai lama untuk form koreksi
        // ...
    }
    
    // Fungsi refresh status GPS
    function refreshGPSStatus() {
        // Cek status GPS dan update tampilan
        // ...
    }
</script>