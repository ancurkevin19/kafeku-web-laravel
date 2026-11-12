# 📘 BLOK 3 — Supervisor: Validasi + PDF Rekap Harian (Detail Teknis, Tanpa Koding)

Tujuan Blok 3: **Supervisor bisa validasi absen timnya (setujui/tolak), dan sistem bisa generate PDF rekap harian.** Fokus: **proses data absen yang sudah masuk dari Blok 2.**

---

## 1. Prasyarat (Harus Sudah dari Blok 2)

| Prasyarat | Status |
|---|---|
| Login & role jalan | ✅ (Blok 1) |
| Tabel `attendances` ada | ✅ (Blok 2) |
| Crew bisa absen masuk & pulang | ✅ (Blok 2) |
| Data absen tersimpan sebagai `pending` | ✅ (Blok 2) |
| Supervisor punya akun login | ✅ (dibuat Owner/HRD di Blok 1) |
| Notifikasi in-app ke supervisor | ✅ (placeholder Blok 2) |

Kalau salah satu belum, **selesaikan Blok 2 dulu.**

---

## 2. Tabel Database (Update)

### 2.1 Tabel `attendances` — kolom baru untuk validasi

Kolom validasi ditambahkan ke tabel yang sudah ada di Blok 2:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `status` | enum | `pending`, `approved`, `rejected`, `disputed` |
| `validated_by` | bigint FK → users | Supervisor yang memvalidasi |
| `validated_at` | datetime nullable | Waktu validasi |
| `alasan_validasi` | text nullable | Alasan supervisor approve/reject |
| `is_notified_supervisor` | boolean | Sudah notifikasi ke supervisor belum |

### Aturan status
| Status | Artinya | Siapa yang ubah |
|---|---|---|
| `pending` | Belum divalidasi | Sistem (saat absen dibuat) |
| `approved` | Disetujui supervisor | Supervisor |
| `rejected` | Ditolak supervisor | Supervisor |
| `disputed` | Perlu klarifikasi | Supervisor / HRD |

### Hasil
- Setiap absen `pending` siap divalidasi supervisor
- Ada jejak siapa & kapan memvalidasi


## 3. Permission Baru (Spatie)

Permission tambahan di Blok 3:

| Permission | Deskripsi |
|---|---|
| `attendance.view.team` | Lihat absensi tim (supervisor) |
| `attendance.validate` | Setujui/tolak absensi tim (supervisor) |
| `attendance.report.pdf` | Generate PDF rekap absensi |

### Mapping Role → Permission (update dari Blok 2)

| Permission | Crew | Supervisor | HRD | Owner |
|---|:---:|:---:|:---:|:---:|
| `attendance.view.team` | ❌ | ✅ | ✅ | ✅ |
| `attendance.validate` | ❌ | ✅ | ❌ | ❌ |
| `attendance.report.pdf` | ❌ | ✅ | ✅ | ✅ |

> `attendance.view.self` dan `attendance.create` tetap dari Blok 2 untuk Crew.

## 4. Halaman: Validasi Absen (Supervisor)

### Fungsi
Supervisor melihat dan memutuskan absen karyawan dalam timnya.

### Alur tampilan
```
Supervisor buka app
  ↓
Dashboard supervisor: "Ada X absen menunggu validasi"
  ↓
Tap notifikasi / menu "Validasi Absen Tim"
  ↓
Daftar absen status `pending` dalam tim supervisor
  ↓
Pilih satu absen → lihat detail (foto masuk, foto pulang, GPS, jarak, jam)
  ↓
Tap "Setujui" atau "Tolak" + masukkan alasan (opsional untuk tolak)
  ↓
Sistem simpan status → notifikasi ke crew (jika ditolak) → kembali ke daftar
```

### Komponen halaman
| Komponen | Fungsi |
|---|---|
| **Filter tanggal** | Pilih hari mana yang divalidasi (default hari ini) |
| **Filter status** | `pending` / semua / `approved` / `rejected` |
| **Daftar absen** | List: nama crew, jam masuk, jam pulang, status, tombol aksi |
| **Detail absen** | Modal/halaman terpisah: foto, GPS, jarak, IP, device |
| **Tombol Setujui** | Ubah status jadi `approved` |
| **Tombol Tolak** | Ubah status jadi `rejected` + isi alasan |
| **Tombol Batalkan** | Kembali tanpa ubah |

### Aturan
- **Supervisor hanya bisa lihat absen timnya** (employee yang under supervisornya — relasi nanti di tabel employees atau via jabatan)
- **Supervisor tidak bisa validasi absen自己 sendiri** (jika supervisor juga karyawan)
- **Setelah di-approved**, absen tidak bisa diubah oleh crew (read-only)
- **Setelah di-rejected**, crew bisa lihat alasan di riwayat absen
- **Tidak ada validasi double** — satu supervisor satu kali validasi per absen

### Hasil
- Setiap absen punya status `approved` atau `rejected`
- Ada alasan kalau ditolak
---

## 5. Halaman: PDF Rekap Harian (DomPDF)

### Fungsi
Generate **PDF rekap absensi harian** untuk laporan harian coffee shop.

### Cara kerja
1. Supervisor / HRD / Owner buka halaman "Rekap Harian PDF"
2. Pilih tanggal (default hari ini)
3. Pilih cakupan: semua karyawan atau tim tertentu (supervisor)
4. Klik "Generate PDF"
5. Sistem ambil data `attendances` pada tanggal tersebut
6. Render ke template HTML → convert ke PDF via DomPDF
7. Download / tampilkan di browser

### Isi PDF Rekap Harian
| Bagian | Isi |
|---|---|
| **Header** | Logo Kafeku, judul "Rekap Absensi Harian", tanggal |
| **Ringkasan** | Total karyawan, hadir, terlambat, absen, belum absen |
| **Tabel detail** | Per karyawan: nama, jabatan, jam masuk, jam pulang, status, terlambat (menit) |
| **Footer** | Tanggal generate, nama admin yang generate, halaman X dari Y |

### Aturan PDF
- **Hanya tanggal yang dipilih** — tidak bisa range tanggal di Blok 3 (range tanggal di Blok 5)
- **Format kertas A4**, portrait
- **Warna brand**: coklat kopi `#6F4E37` + krem `#F5EFE6`
- **Nama file**: `rekap-absen-YYYY-MM-DD.pdf`
- **Disimpan di storage** (opsional, untuk audit) + langsung download
- **Tidak bisa diedit** setelah generate — ini adalah snapshot

### Library
- **DomPDF** (barryvdh/laravel-dompdf) — sudah diinstall di Blok 1

### Hasil
- Ada file PDF rekap absensi harian
- Siap untuk print atau kirim ke Owner

## 6. Notifikasi In-App (Fungsional di Blok 3)

### Update dari placeholder Blok 2

| Aksi | Notifikasi ke | Pesan |
|---|---|---|
| Absen baru masuk | Supervisor tim | "Budi absen masuk 08:03" |
| Absen baru pulang | Supervisor tim | "Budi absen pulang 17:05" |
| Absen disetujui | Crew | "Absen kamu disetujui supervisor" |
| Absen ditolak | Crew | "Absen kamu ditolak: [alasan]" |

### Alur
1. Absen baru dibuat → notifikasi otomatis ke supervisor (Blok 2 sudah siap dasarnya)
2. Supervisor klik notifikasi → buka halaman validasi detail absen tsb
3. Supervisor setujui/tolak → notifikasi otomatis ke crew

### Aturan
- Notifikasi disimpan di database (tabel `notifications` atau pakai Laravel Notification)
- **Belum ada push notification / email / WhatsApp** di Blok 3
- Badge counter di bell icon

## 7. Layout & Sidebar (Update dari Blok 2)

Menu baru yang muncul di sidebar Blok 3:

| Role | Menu Baru (Blok 3) |
|---|---|
| **Owner** | Dashboard, Karyawan, Absensi (Semua), Validasi Absen, Rekap PDF, Pengaturan, Kelola User, Activity Log |
| **HRD** | Dashboard, Karyawan, Absensi (Semua), Rekap PDF, Activity Log |
| **Supervisor** | Dashboard, Tim Saya, Absensi Tim, Validasi Absen, Rekap PDF (tim) |
| **Crew** | Dashboard, Profil Saya, Absen Masuk, Absen Pulang, Riwayat Absen |

> Menu "Validasi Absen" dan "Rekap PDF" ditambahkan di Blok 3.

## 8. Halaman yang Harus Jadi di Blok 3

| # | Halaman | Role | Fungsi |
|---|---|---|---|
| 1 | **Validasi Absen Tim** | Supervisor | Lihat & setujui/tolak absen pending tim |
| 2 | **Detail Absen untuk Validasi** | Supervisor | Lihat foto, GPS, jarak sebelum keputusan |
| 3 | **Rekap Absensi Harian PDF** | Supervisor, HRD, Owner | Generate & download PDF |
| 4 | **Notifikasi** | Semua | Lihat notifikasi in-app (bell) |

### Aturan
- Halaman **Validasi Absen** harus menampilkan foto absen besar + peta lokasi kecil
- Tombol Setujui/Tolak **jelas & berwarna** (hijau / merah)
- PDF **download otomatis** setelah generate
- Crew lihat status absen di riwayat: "Disetujui" / "Ditolak: [alasan]"

## 9. Activity Log (Update)

Aksi baru yang dicatat di `activity_logs`:

| Aksi | Model | Deskripsi |
|---|---|---|
| `attendance_validate` | `attendances` | Supervisor setujui/tolak absensi |
| `attendance_report_pdf` | `attendances` | Generate PDF rekap harian |

Data lama & data baru disimpan dalam JSON:
- `data_lama`: status sebelum (`pending`)
- `data_baru`: status setelah (`approved`/`rejected`) + alasan

## 10. Seeder Bootstrap (Update Blok 3)

Tambahan data yang di-seed (jika belum ada dari Blok 2):

| Urutan | Data | Kenapa |
|---|---|---|
| 8 | **Permissions validasi & PDF** | `attendance.view.team`, `attendance.validate`, `attendance.report.pdf` |
| 9 | **Mapping role → permission** | Hubungkan role dengan permission baru |
| 10 | **Settings absensi default** | `office_lat`, `office_lng`, `office_radius_meter`, `jam_masuk`, `jam_pulang`, `toleransi_terlambat_menit`, `wajib_selfie`, `wajib_gps` |

## 11. Kriteria Selesai Blok 3 (Definition of Done)

Blok 3 **selesai** kalau semua ini lolos:

| # | Kriteria | Cara Uji |
|---|---|---|
| 1 | Supervisor lihat absen pending timnya | Login supervisor → buka Validasi → daftar absen muncul |
| 2 | Supervisor setujui absen | Pilih absen → Setujui → status jadi `approved` |
| 3 | Supervisor tolak absen + alasan | Pilih absen → Tolak + isi alasan → status `rejected` + alasan tersimpan |
| 4 | Crew lihat hasil validasi | Login crew → buka Riwayat → status berubah `approved`/`rejected` + alasan |
| 5 | Generate PDF rekap harian | Buka Rekap PDF → pilih tanggal → download PDF |
| 6 | PDF berisi rekap lengkap | Buka PDF → ada nama, jam masuk, pulang, status, terlambat |
| 7 | Supervisor tidak bisa validasi absen tim lain | Coba akses absen karyawan bukan timnya → 403 |
| 8 | Crew tidak bisa akses halaman validasi | Akses URL validasi sebagai Crew → 403 |
| 9 | Notifikasi muncul kalau ada absen baru | Login supervisor → bell ada notifikasi absen baru |
| 10 | Notifikasi validasi ke crew | Supervisor setujui → crew dapat notifikasi "disetujui" |
| 11 | Activity log mencatat validasi | Setelah setujui/tolak → cek Activity Log → ada record `attendance_validate` |
| 12 | PDF generate cepat (< 5 detik) | Klik generate → PDF siap dalam 5 detik |
| 13 | Tampilan validasi jalan di HP | Buka di Android/iPhone → tombol setujui/tolak bisa diklik |
| 14 | PDF bisa di-print / dibuka di HP | Buka PDF di HP → bisa dibaca, tidak rusak |

**Kalau 14 poin ini lolos → Blok 3 selesai, lanjut Blok 4 (HRD: Verifikasi & Koreksi).**

## 12. Yang TIDAK Dibuat di Blok 3

Supaya fokus, ini **jangan** dibuat dulu:

| ❌ Jangan | Alasan |
|---|---|
| Verifikasi HRD | Blok 4 |
| Koreksi absen | Blok 4 |
| Rekap bulanan PDF | Blok 5 (range tanggal) |
| Email PDF otomatis | Blok 5 atau 6 |
| Push notification PWA | Blok 6 |
| WhatsApp notifikasi | Opsional, nanti |
| Dashboard statistik Owner | Blok 5 |
| Multi-shift absen | Blok 6 |
| Face recognition | Overkill |

## 13. Urutan Pengerjaan di Blok 3

| Langkah | Yang Dikerjakan | Hasil |
|---|---|---|
| 1 | Migration: tambah kolom validasi ke `attendances` | Tabel siap untuk validasi |
| 2 | Seeder: permissions validasi + PDF + mapping | RBAC siap |
| 3 | Middleware: cek akses validasi (supervisor hanya timnya) | Akses terkontrol |
| 4 | Halaman Validasi Absen Tim (Supervisor) | Daftar absen pending muncul |
| 5 | Detail Absen untuk Validasi (foto + GPS) | Supervisor bisa lihat bukti |
| 6 | Aksi Setujui / Tolak + alasan | Status absen berubah |
| 7 | Notifikasi ke crew setelah validasi | Crew tahu hasil |
| 8 | Install & konfigurasi DomPDF | Library PDF siap |
| 9 | Halaman Rekap Absensi Harian PDF | Form pilih tanggal + generate |
| 10 | Template HTML → PDF (DomPDF) | PDF rekap harian bisa diunduh |
| 11 | Update sidebar dinamis | Menu validasi + PDF muncul |
| 12 | Activity log otomatis untuk validasi & PDF | Audit jalan |
| 13 | Uji 14 kriteria selesai | Blok 3 done |

## 14. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| **Supervisor validasi absen yang bukan timnya** | Middleware cek relasi supervisor → employee |
| **PDF generate lama (banyak data)** | Batasi 1 tanggal saja, tidak range tanggal |
| **Foto tidak muncul di PDF** | Gunakan absolute URL atau base64 embed di HTML |
| **Supervisor lupa validasi, absen menumpuk** | Notifikasi in-app + daftar pending selalu terlihat |
| **Crew tidak tahu absen ditolak** | Notifikasi otomatis + alasan ditampilkan di riwayat |
| **DomPDF error pada font khusus** | Gunakan font standar (Arial/Helvetica) atau font Indonesia yang didukung |

## 15. Ringkasan Blok 3

| Aspek | Isi |
|---|---|
| **Tujuan** | Supervisor validasi absen tim + generate PDF rekap harian |
| **Tabel update** | `attendances` (tambah kolom status, validated_by, validated_at, alasan_validasi) |
| **Permission baru** | `attendance.view.team`, `attendance.validate`, `attendance.report.pdf` |
| **Halaman baru** | Validasi Absen Tim, Detail Absen, Rekap PDF, Notifikasi |
| **PDF library** | DomPDF (barryvdh/laravel-dompdf) |
| **Format PDF** | A4 portrait, rekap harian, warna brand Kafeku |
| **Notifikasi** | In-app ke supervisor & crew (fungsional) |
| **Kriteria selesai** | 14 poin |
| **Yang belum** | Verifikasi HRD, koreksi, rekap bulanan, email PDF, push notification |

---

## 🎯 Setelah Blok 3 Selesai

Anda sudah punya:
- ✅ Aplikasi bisa login (Blok 1)
- ✅ Crew bisa absen dari HP (Blok 2)
- ✅ Supervisor bisa validasi absen (Blok 3)
- ✅ Ada PDF rekap harian (Blok 3)
- ✅ Notifikasi in-app fungsional (Blok 3)

**Yang belum:**
- ❌ HRD belum bisa verifikasi (Blok 4)
- ❌ HRD belum bisa koreksi absen (Blok 4)
- ❌ Belum ada rekap bulanan (Blok 5)
- ❌ Owner belum lihat dashboard statistik (Blok 5)

**Blok 3 = data diproses supervisor + laporan PDF.**

---

Mau saya lanjutkan ke **Blok 4 — HRD: Verifikasi & Koreksi Absen** dengan detail teknis yang sama?