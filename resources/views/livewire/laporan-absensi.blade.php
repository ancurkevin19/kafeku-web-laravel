<div>
    <h3>PDF Laporan Absensi</h3>
    
    <div class="row mb-3">
        <div class="col-12">
            <select class="form-select" wire:model="filterTanggalAwal">
                <option value="bulan ini">Bulan Ini</option>
                <option value="minggu ini">Minggu Ini</option>
                <option value="hari ini">Hari Ini</option>
                <option kustom>Kustom</option>
            </select>
            <input type="date" class="form-control form-control-sm ms-2" wire:model="filterTanggalAwalCustom" style="width: 120px;">
            <input type="date" class="form-control form-control-sm ms-2" wire:model="filterTanggalAkhirCustom" style="width: 120px;">
            <button type="button" class="btn btn-outline-primary btn-sm ms-2" wire:click="generatePDF">Generate PDF</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-sm">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Karyawan</th>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Status</th>
                    <th>Validasi</th>
                    <th>Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($laporan as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->employee->name }}</td>
                    <td>{{ $item->tanggal }}</td>
                    <td>{{ $item->jam_masuk }}</td>
                    <td>{{ $item->jam_pulang }}</td>
                    <td>
                        <span class="badge bg-{{ in_array($item->status_masuk, ['tepat_waktu']) ? 'success' : 'warning' }}">
                            {{ $item->status_masuk }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $item->status_validasi == 'disetujui' ? 'primary' : 'secondary' }}">
                            {{ $item->status_validasi }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $item->status_verifikasi == 'diverifikasi' ? 'success' : 'secondary' }}">
                            {{ $item->status_verifikasi }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (count($laporan) == 0)
        <div class="alert alert-info">
            Tidak ada data untuk periode yang dipilih.
        </div>
    @endif
</div>

<script>
    window.Livewire.on('init', () => {
        loadLaporan();
    });

    function loadLaporan() {
        // Load laporan absensi berdasarkan filter tanggal
        // ...
    }

    async function generatePDF() {
        // Trigger PDF generation via Livewire
        // Bisa menggunakan library jsPDF atau mengirim ke backend
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Header PDF
        doc.setFontSize(18);
        doc.text('Laporan Absensi Kafeku', 14, 15);
        
        // Tambah tabel ke PDF
        // ...
        
        doc.save('Laporan-Absensi-{{ date("Y-m-d") }}.pdf');
    }
</script>