<div>
    <h3>Absensi Masuk</h3>
    
    <div class="row">
        <div class="col-12">
            <button 
                wire:click="checkGPS"
                class="btn btn-primary mb-3">
                <i class="fas fa-location-arrow me-2"></i>
                Cek Lokasi GPS
            </button>
        </div>
    </div>

    <div id="gps-status" class="alert alert-info d-none">
        <i class="fas fa-spinner fa-spin me-2"></i>
        Sedang mendapatkan lokasi...
    </div>

    <div id "location-error" class="alert alert-danger d-none">
        <i class="fas fa-exclamation-triangle me-2"></i>
        GPS tidak aktif. Aktifkan di pengaturan HP.
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Absensi Masuk</h5>
        </div>
        <div class="card-body">
            <form wire:submit="absenMasuk">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Foto Selfie</label>
                            <input type="file" class="form-control" wire:model="fotoSelfie" accept="image/*">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Validasi Radius</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="radius" id="radiusYa" value="ya" wire:model="radiusPilih">
                                <label class="form-check-label" for="radiusYa">Ya, absen meski luar radius</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="radius" id="radiusTidak" value="tidak" wire:model="radiusPilih" checked>
                                <label class="form-check-label" for="radiusTidak">Tidak, harus di dalam radius</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success mt-3">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Absen Masuk
                </button>
            </form>
        </div>
    </div>

    <script>
        // Inisialisasi GPS dan waktu server saat halaman dimuat
        window.addEventListener('load', () => {
            // Set interval update waktu server tiap detik
            window.serverWaktuInterval = setInterval(refreshServerWaktu, 1000);
            refreshServerWaktu();
            
            // Cek permission kamera dan GPS
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        // Kamera berhasil diakses
                        previewStream(stream);
                        showGPSStatus('siap', 'Akurasi: sedang');
                    })
                    .catch(err => {
                        console.error('Kamera/GPS tidak diakses:', err);
                        showGPSStatus('mati', 'Lokasi tidak aktif. Aktifkan di pengaturan HP.');
                    });
            } else {
                showGPSStatus('mati', 'Lokasi tidak aktif. Aktifkan di pengaturan HP.');
            }
        });

        // Fungsi refresh waktu server tiap detik
        function refreshServerWaktu() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('server-waktu').textContent = now.toLocaleDateString('id-ID', options) + ' ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit });
        }

        // Fungsi show status GPS
        function showGPSStatus(status, akurasi) {
            const display = document.getElementById('gps-status-display');
            const error = document.getElementById('gps-status-error');
            const statusText = document.getElementById('gps-status-text');
            const akurasiEl = document.getElementById('gps-akurasi');
            const pesanEl = document.getElementById('gps-status-pesan');
            
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
        function previewStream(stream) {
            const video = document.querySelector('video');
            if (video) {
                video.srcObject = stream;
            }
        }
    </script>
</div>