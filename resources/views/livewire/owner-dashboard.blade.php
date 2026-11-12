<div>
    <h3>Owner Dashboard</h3>
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card p-4">
                <h5>Ringkasan Bulanan</h5>
                <div class="row">
                    <div class="col-6">
                        <h3 class="text-primary mb-0" id="total-absensi-bulan">0</h3>
                        <small class="text-muted">Total Absensi</small>
                    </div>
                    <div class="col-6">
                        <h3 class="text-success mb-0" id="total-terlambat-bulan">0</h3>
                        <small class="text-muted">Terlambat</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Absensi per Role</h5>
                </div>
                <div class="card-body">
                    <canvas id="absensiPerRoleChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Absensi Bulanan (Grafik)</h5>
                </div>
                <div class="card-body">
                    <canvas id="absensiBulananChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.Livewire.on('init', () => {
        loadOwnerDashboard();
    });

    async function loadOwnerDashboard() {
        // Fetch data untuk Owner Dashboard
        // Absensi total per bulan
        // Absensi per role (crew/supervisor/hrd/owner)
        // Grafik tren bulanan
        
        // Example API call
        const response = await fetch('/api/owner/dashboard', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        // Update total absensi
        document.getElementById('total-absensi-bulan').textContent = data.total_absensi || 0;
        document.getElementById('total-terlambat-bulan').textContent = data.total_terlambat || 0;
        
        // Initialize Charts
        initAbsensiPerRoleChart(data.per_role);
        initAbsensiBulananChart(data.bulanan);
    }

    function initAbsensiPerRoleChart(perRoleData) {
        const ctx = document.getElementById('absensiPerRoleChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Crew', 'Supervisor', 'HRD', 'Owner'],
                datasets: [{
                    label: 'Jumlah Absensi',
                    data: perRoleData.map(r => r.count),
                    backgroundColor: [
                        'rgba(111, 78, 55, 0.8)',  // Primary - Coklat kopi
                        'rgba(166, 123, 91, 0.8)', // Primary Light
                        'rgba(245, 239, 230, 0.8)', // Secondary - Krem
                        'rgba(255, 255, 255, 0.8)'  // Surface - Putih
                    ],
                    borderColor: [
                        'rgba(111, 78, 55, 1)',
                        'rgba(166, 123, 91, 1)',
                        'rgba(245, 239, 230, 1)',
                        'rgba(255, 255, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    },
                    title: {
                        display: true,
                        text: 'Absensi per Role'
                    }
                }
            }
        });
    }

    function initAbsensiBulananChart(bulananData) {
        const ctx = document.getElementById('absensiBulananChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: bulananData.map(d => d.bulan),
                datasets: [{
                    label: 'Absensi Masuk',
                    data: bulananData.map(d => d.absensi_masuk),
                    borderColor: 'rgba(111, 78, 55, 1)',
                    backgroundColor: 'rgba(111, 78, 55, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Absensi Pulang',
                    data: bulananData.map(d => d.absensi_pulang),
                    borderColor: 'rgba(166, 123, 91, 1)',
                    backgroundColor: 'rgba(166, 123, 91, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    },
                    title: {
                        display: true,
                        text: 'Tren Absensi Bulanan'
                    }
                }
            }
        });
    }
</script>