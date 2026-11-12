<div>
    <h3>Notifikasi Push & WhatsApp</h3>
    
    <div class="row mb-3">
        <div class="col-12">
            <select class="form-select" wire:model="filterTipeNotif">
                <option value="semua">Semua Notifikasi</option>
                <option value="absensi">Notifikasi Absensi</option>
                <option value="verifikasi">Notifikasi Verifikasi</option>
                <option value="koreksi">Notifikasi Koreksi</option>
            </select>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>Karyawan</th>
                    <th>Mesaj</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($notifikasi as $notif)
                <tr>
                    <td>{{ Carbon::parse($notif->created_at)->format('d M H:i') }}</td>
                    <td>
                        <span class="badge bg-{{ $notif->tipe == 'absensi' ? 'primary' : ($notif->tipe == 'verifikasi' ? 'success' : 'warning') }}">
                            {{ ucfirst($notif->tipe) }}
                        </span>
                    </td>
                    <td>{{ $notif->employee->name }}</td>
                    <td>{{ substr($notif->pesan, 0, 50) . (strlen($notif->pesan) > 50 ? '...' : '') }}</td>
                    <td>
                        <span class="badge bg-{{ $notif->status_baca == 'sudah' ? 'secondary' : 'primary' }}">
                            {{ ucfirst($notif->status_baca) }}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-link text-primary">
                            <i class="fas fa-eye me-1"></i>
                            Baca
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (count($notifikasi) == 0)
        <div class="alert alert-info">
            Tidak ada notifikasi.
        </div>
    @endif
</div>

<script>
    window.Livewire.on('init', () => {
        loadNotifikasi();
    });

    function loadNotifikasi() {
        // Load notifikasi berdasarkan tipe filter
        // ...
    }

    function kirimWhatsApp(contact, pesan) {
        // Buka WhatsApp Web dengan pesan terformat
        const url = `https://wa.me/${contact}?text=${encodeURIComponent(pesan)}`;
        window.open(url, '_blank');
    }

    function triggerPushNotification(title, body) {
        // Trigger PWA Push Notification
        if ('serviceWorker' in navigator && 'PushManager' in window.ServiceWorkerRegistration.prototype) {
            // Already registered push service
            navigator.serviceWorker.ready.then(registration => {
                registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    serverPublicKey: 'YOUR_VAPID_PUBLIC_KEY'
                });
            });
        }
        
        // Show notification jika izin ada
        if (Notification.permission === 'granted') {
            navigator.serviceWorker.ready.then(registration => {
                registration.showNotification(title, {
                    body: body,
                    icon: '/favicon.ico',
                    vibrate: [200, 100, 200]
                });
            });
        }
    }
</script>