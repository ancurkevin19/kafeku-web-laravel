<div>
    <h3>Absensi Pulang</h3>
    
    <div class="row">
        <div class="col-12">
            <button 
                wire:click="checkGPSPulang"
                class="btn btn-primary mb-3">
                <i class="fas fa-location-arrow me-2"></i>
                Cek Lokasi GPS untuk Pulang
            </button>
        </div>
    </div>

    <div id "gps-status-pulang" class="alert alert-info d-none">
        <i class="fas fa-spinner fa-spin me-2"></i>
        Sedang mendapatkan lokasi...
    </div>

    <div id "location-error-pulang" class="alert alert-danger d-none">
        <i class="fas fa-exclamation-triangle me-2"></i>
        GPS tidak aktif. Aktifkan di pengaturan HP.
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Absensi Pulang</h5>
        </div>
        <div class="card-body">
            <form wire:submit="absenPulang">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Foto Selfie</label>
                            <input type="file" class="form-control" wire:model="fotoSelfiePulang" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Validasi Radius</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="radius" id="radiusYa2" value="ya" wire:model="radiusPilih2">
                                <label class="form-check-label" for="radiusYa2">Ya, absen pulang meski luar radius</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="radius" id="radiusTidak2" value="tidak" wire:model="radiusPilih2" checked>
                                <label class="form-check-label" for="radiusTidak2">Tidak, harus di dalam radius</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Absen Pulang
                </button>
            </form>
        </div>
    </div>

    <script>
        // Inisialisasi GPS dan waktu server saat halaman dimuat
        window.addEventListener('load', () => {
            // Set interval update waktu server tiap detik
            window.serverWaktuInterval = setInterval(refreshServerWaktuPulang, 1000);
            refreshServerWaktuPulang();
            
            // Cek permission kamera dan GPS
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        // Kamera berhasil diakses
                        previewStreamPulang(stream);
                        showGPSStatusPulang('siap', 'Akurasi: sedang');
                    })
                    .catch(err => {
                        console.error('Kamera/GPS tidak diakses:', err);
                        showGPSStatusPulang('mati', 'Lokasi tidak aktif. Aktifkan di pengaturan HP.');
                    });
            } else {
                showGPSStatusPulang('mati', 'Lokasi tidak aktif. Aktifkan di pengaturan HP.');
            }
        });

        // Fungsi refresh waktu server tiap detik
        function refreshServerWaktuPulang() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('server-waktu-pulang').textContent = now.toLocaleDateString('id-ID', options) + ' ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit });
        }

        // Fungsi show status GPS untuk pulang
        function showGPSStatusPulang(status, akurasi) {
            const display = document.getElementById('gps-status-display-pulang');
            const error = document.getElementById('gps-status-error-pulang');
            const statusText = document.getElementById('gps-status-text-pulang');
            const akurasiEl = document.getElementById('gps-akurasi-pulang');
            const pesanEl = document.getElementById('gps-status-pesan-pulang');
            
            const states = {
                'siap': { text: 'Dalam radius', class: 'text-success' },
                'mati': { text: 'GPS mati', class: 'text-danger' },
                'loading': { text: 'Mencari GPS', class: 'text-warning' },
                'luar': { text: 'Di luar radius', class: 'text-danger' }
            };
            
            if (states[status]) {
                const s = states[status];
                statusText.textContent = s.text;
                statusText.className = `me-2 ${s.class}`;
                akurasiEl.textContent = akurasi;
                display.classList.remove('d-none');
                error.classList.add('d-none');
            } else {
                display.classList.add('d-none');
                error.classList.remove('d-none');
                pesanEl.textContent = status;
            }
        }

        // Fungsi preview stream kamera
        function previewStreamPulang(stream) {
            const video = document.querySelector('video');
            if (video) {
                video.srcObject = stream;
            }
        }
    </script>
</div>