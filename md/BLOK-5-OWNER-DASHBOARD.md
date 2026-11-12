# 📘 BLOK 5 — Owner: Dashboard + Pengaturan + Rekap Bulanan (Detail Teknis, Tanpa Koding)

Tujuan Blok 5: **Owner bisa melihat hasil akhir absensi, mengatur konfigurasi aplikasi, dan menerima Rekap Bulanan otomatis.** Ini blok **penutup siklus absensi** — setelah ini, satu putaran lengkap Crew → Supervisor → HRD → Owner selesai. Fokus: **visibilitas, kontrol, dan laporan strategis.**

---

## 1. Prasyarat (Harus Sudah dari Blok 1–4)

| Prasyarat | Status |
|---|---|
| Login, role, permission jalan | ✅ Blok 1 |
| Crew absen (`pending`) | ✅ Blok 2 |
| Supervisor validasi (`validated`) | ✅ Blok 3 |
| HRD verifikasi (`verified` / `corrected` / `disputed`) | ✅ Blok 4 |
| PDF Rekap Final terkirim ke Owner | ✅ Blok 4 |
| Data absen `final` terkunci | ✅ Blok 4 |
| Tabel `attendances`, `attendance_reports` terisi | ✅ Blok 4 |

Kalau HRD belum bisa verifikasi, **Blok 4 belum selesai** — jangan lanjut.

---

## 2. Konsep Peran Owner (Pahami Dulu)

### Apa tugas Owner?
| Tugas | Deskripsi |
|---|---|
| **Melihat** | Dashboard, laporan, tren |
| **Mengatur** | Konfigurasi aplikasi (lokasi, jam, toleransi) |
| **Mengelola** | User & role |
| **Mengawasi** | Activity log, audit |
| **Menyetujui** | (opsional) koreksi besar, gaji |

### Yang BUKAN tugas Owner
- ❌ Verifikasi absen harian → HRD (Blok 4)
- ❌ Validasi absen → Supervisor (Blok 3)
- ❌ Absen → Crew (kecuali Owner juga karyawan)
- ❌ Koreksi harian → HRD (Blok 4)

### Prinsip Owner
- **Read-heavy, write-light** — banyak lihat, sedikit ubah
- **Strategis, bukan operasional** — fokus tren, bukan detail harian
- **Kontrol penuh** — bisa ubah setting, user, role
- **Audit** — bisa lihat semua yang terjadi
- **Tidak mengganggu operasional** — Owner tidak perlu validasi harian

## 3. Halaman: Dashboard Owner (Utama)

### Fungsi
Halaman pertama Owner setelah login — **ringkasan bisnis & HR dalam 1 layar.**

### Alur tampilan
```
Owner login
  ↓
Langsung ke Dashboard Owner
  ↓
Kartu-kartu statistik:
  ├── Kehadiran hari ini
  ├── Kehadiran bulan ini
  ├── Terlambat bulan ini
  ├── Absen/disputed bulan ini
  ├── Total biaya gaji bulan ini (placeholder Blok 6)
  └── Karyawan aktif
  ↓
Grafik tren kehadiran (7 hari / 30 hari)
  ↓
Daftar "Perlu perhatian":
  ├── Crew terlambat berulang
  ├── Absen disputed > 3 hari
  └── Kontrak habis < 30 hari
  ↓
Pintasan: [Lihat Laporan] [Unduh Rekap Bulanan] [Pengaturan]
```

### Komponen dashboard
| Komponen | Isi | Sumber Data |
|---|---|---|
| **Kartu Kehadiran Hari Ini** | "8/10 hadir · 2 terlambat" | `attendances` hari ini |
| **Kartu Kehadiran Bulan Ini** | Persentase kehadiran | `attendances` bulan ini |
| **Kartu Terlambat** | Jumlah & crew terbanyak | `attendances.is_terlambat` |
| **Kartu Disputed** | Jumlah yang masih disputed | `attendances.status` |
| **Kartu Karyawan Aktif** | Jumlah karyawan `aktif` | `employees.status` |
| **Grafik Tren** | Line chart kehadiran 30 hari | `attendances` agregat |
| **Daftar "Perlu Perhatian"** | Alert berbasis aturan | Query khusus |
| **Pintasan** | Tombol ke halaman penting | — |

### Aturan UX
- **Mobile-friendly** — Owner mungkin buka dari HP
- **Angka besar & jelas** — mudah dibaca
- **Warna status** — hijau (baik), kuning (warning), merah (masalah)
- **Refresh data** — tombol refresh manual, atau auto-refresh tiap 5 menit
- **Tidak menampilkan foto crew** — dashboard ringkas, detail ada di halaman lain
- **Link ke detail** — setiap kartu bisa diklik untuk lihat detail

### Aturan data
- **Agregat saja** — jangan query berat per karyawan
- **Cache 5 menit** — kurangi beban database
- **Filter outlet** — kalau nanti multi-outlet (sekarang 1 outlet)
- **Periode default** — bulan ini

### Hasil
- Owner buka app → langsung tahu kondisi HR
- Bisa ambil keputusan cepat

## 4. Halaman: Laporan Absensi (Owner)

### Fungsi
Owner bisa **melihat & mengunduh laporan absensi** — per hari, per bulan, per karyawan.

### Alur tampilan
```
Owner buka menu "Laporan"
  ↓
Pilih jenis laporan:
  ├── Harian (pilih tanggal)
  ├── Bulanan (pilih bulan)
  ├── Per Karyawan (pilih karyawan + periode)
  └── Rekap Disputed (semua yang disputed)
  ↓
Preview di layar (tabel ringkas)
  ↓
Tombol: [Unduh PDF] [Export Excel]
```

### Jenis laporan
| Jenis | Isi | Sumber |
|---|---|---|
| **Harian** | Semua absen 1 hari | PDF Rekap Final (sudah ada) |
| **Bulanan** | Semua absen 1 bulan | Generate baru (lihat bagian 6) |
| **Per Karyawan** | Absen 1 karyawan dalam periode | Query khusus |
| **Disputed** | Semua absen disputed | Query `status='disputed'` |

### Komponen halaman
| Komponen | Fungsi |
|---|---|
| **Filter jenis laporan** | Dropdown |
| **Filter periode** | Date picker / month picker |
| **Filter karyawan** | Dropdown (untuk per karyawan) |
| **Preview tabel** | Ringkas di layar |
| **Tombol unduh PDF** | Unduh laporan |
| **Tombol export Excel** | (opsional) Export untuk analisis |

### Aturan UX
- **Preview dulu, unduh kemudian** — Owner bisa lihat cepat tanpa unduh
- **PDF sudah ada** — untuk Harian, pakai PDF Blok 4
- **PDF baru** — untuk Bulanan (lihat bagian 6)
- **Export Excel** — opsional, bisa ditunda ke Blok 6
- **Loading state** — kalau query berat, tampilkan loading

### Aturan data
- **Hanya data `final`** — jangan tampilkan data mentah
- **Cache 1 jam** — laporan bulanan berat, cache
- **Filter default** — bulan ini

### Hasil
- Owner bisa lihat & unduh laporan kapan saja
- Tidak perlu minta HRD

## 5. Halaman: Pengaturan (Owner)

### Fungsi
Owner bisa **mengubah konfigurasi aplikasi** tanpa coding.

### Kategori pengaturan
| Kategori | Setting | Contoh |
|---|---|---|
| **Umum** | Nama aplikasi, tagline | Kafeku, tagline |
| **Absensi** | Lokasi, radius, jam kerja, toleransi | Lat/lng, 100 m, 08:00–17:00, 10 menit |
| **Absensi** | Wajib selfie, wajib GPS | true/false |
| **Notifikasi** | Email penerima PDF | Email HRD, Owner |
| **Email** | SMTP (kalau mau ubah) | Host, port, user |
| **Tampilan** | Logo, warna | (opsional, bisa ditunda) |

### Alur tampilan
```
Owner buka "Pengaturan"
  ↓
Tab kategori:
  ├── Umum
  ├── Absensi
  ├── Notifikasi
  └── Email
  ↓
Edit field → simpan
  ↓
Konfirmasi → tersimpan → activity log
```

### Komponen halaman
| Komponen | Fungsi |
|---|---|
| **Tab kategori** | Navigasi antar kategori |
| **Form field** | Input per setting |
| **Validasi** | Angka harus angka, lat/lng valid |
| **Tombol simpan** | Simpan perubahan |
| **Tombol reset** | Kembali ke default |
| **Info "terakhir diubah"** | Siapa & kapan |

### Aturan UX
- **Tab jelas** — Owner tidak bingung
- **Validasi ketat** — lat/lng harus -90..90 / -180..180, radius > 0, jam format HH:MM
- **Konfirmasi sebelum simpan** — "Yakin ubah lokasi absen? Semua crew akan pakai lokasi baru."
- **Preview peta** — untuk lat/lng, tampilkan peta kecil supaya Owner yakin
- **Tidak bisa ubah setting sensitif tanpa konfirmasi** — mis. radius, jam kerja

### Aturan data
- **Hanya Owner** bisa akses (permission `setting.manage`)
- **Setiap perubahan** dicatat di `activity_logs` (nilai lama vs baru)
- **Cache di-clear** setelah simpan — supaya perubahan langsung berlaku
- **Setting tidak bisa dihapus** — hanya diubah

### Hasil
- Owner bisa atur aplikasi tanpa developer
- Perubahan tercatat

## 6. Rekap Bulanan — Generate & Distribusi

### Fungsi
PDF **Rekap Absensi Bulanan** otomatis dibuat tiap awal bulan — ringkasan 1 bulan penuh.

### Kapan trigger?
| Kondisi | Aksi |
|---|---|
| **Tanggal 1 tiap bulan, 06:00** (cron) | Auto-generate Rekap Bulanan bulan lalu |
| **Owner klik "Generate Rekap Bulanan"** (manual) | Generate untuk bulan tertentu |

### Data yang diagregasi
| Data | Sumber |
|---|---|
| Total hadir per crew | `attendances` count `final` per crew |
| Total terlambat per crew | `attendances.is_terlambat` count |
| Total menit terlambat | `attendances.menit_terlambat` sum |
| Total disputed | `attendances.status='disputed'` |
| Total koreksi | `attendance_corrections` count |
| Total absen (tidak hadir) | Hari kerja − hadir |
| Persentase kehadiran | hadir / hari kerja × 100 |
| Jam kerja total | sum jam pulang − jam masuk |
| Lembur (opsional) | jam pulang − jam kerja normal |

### Isi PDF Rekap Bulanan
| Bagian | Isi |
|---|---|
| **Header** | Logo Kafeku + "REKAP ABSENSI BULANAN" |
| **Info** | Periode · Total crew · Hari kerja |
| **Tabel utama** | No · Nama · Posisi · Hadir · Terlambat · Menit Terlambat · Disputed · Koreksi · % Kehadiran |
| **Ringkasan** | Total hadir · Total terlambat · Rata-rata kehadiran |
| **Top 3 Terlambat** | Crew dengan menit terlambat terbanyak |
| **Top 3 Kehadiran** | Crew dengan kehadiran terbaik |
| **Catatan** | (opsional) dari HRD |
| **Footer** | Nomor dokumen · Waktu dibuat · Halaman · `© 2026 Kafeku · by AchmatFadilah Dev` |

### Nama file
```
KFK-ABB-{YYYY-MM}-{urut}.pdf
Contoh: KFK-ABB-2026-11-001.pdf
```

### Distribusi
| Penerima | Kanal |
|---|---|
| **Owner** | In-app + email (attach PDF) |
| **HRD** | In-app + email (attach PDF) |
| **Supervisor** | In-app + email (attach PDF, ringkas) |

### Aturan
- **1 PDF per bulan** — tidak dobel
- **Hanya data `final`** — data pending/disputed tidak masuk
- **Kalau ada koreksi susulan**, regenerate PDF (dengan nomor baru)
- **PDF disimpan permanen** — arsip

### Hasil
- Owner dapat rekap bulanan otomatis
- HRD punya arsip
- Supervisor tahu performa tim

## 7. Halaman: Kelola User & Role (Owner)

### Fungsi
Owner bisa **membuat akun, assign role, aktif/nonaktifkan user.**

> **Catatan:** Halaman ini sudah ada di Blok 1 (bootstrap). Di Blok 5, **diperluas** dengan fitur assign multi-role dan audit.

### Fitur tambahan di Blok 5
| Fitur | Fungsi |
|---|---|
| **Assign multi-role** | 1 user bisa punya >1 role (mis. Owner + HRD) |
| **Aktif/nonaktif user** | Nonaktifkan tanpa hapus |
| **Reset password** | Paksa user ganti password |
| **Lihat aktivitas user** | Activity log per user |
| **Riwayat role** | Siapa ubah role kapan |

### Aturan UX
- **Hanya Owner** bisa akses (permission `user.manage`)
- **Konfirmasi sebelum ubah role** — "Yakin jadikan Budi Supervisor?"
- **Tidak bisa hapus user yang punya data** — nonaktifkan saja
- **Tidak bisa hapus diri sendiri** — Owner tidak bisa nonaktifkan diri sendiri
- **Minimal 1 Owner aktif** — sistem tolak kalau mau nonaktifkan Owner terakhir

### Aturan data
- **Setiap perubahan role** dicatat di `activity_logs`
- **Password reset** — generate link reset, kirim email
- **Nonaktif user** — user tidak bisa login, tapi data tetap ada

### Hasil
- Owner bisa kelola user dengan aman
- Tidak bisa "mengunci diri sendiri"

## 8. Halaman: Activity Log (Owner)

### Fungsi
Owner bisa **melihat semua aktivitas** di aplikasi — untuk audit & pengawasan.

> **Catatan:** Activity log sudah ada di Blok 1 (tabel + pencatatan). Di Blok 5, **diperluas** dengan halaman khusus Owner.

### Fitur
| Fitur | Fungsi |
|---|---|
| **Filter user** | Lihat aktivitas user tertentu |
| **Filter aksi** | login, create, update, delete, validate, verify, correct |
| **Filter model** | users, employees, attendances, settings |
| **Filter tanggal** | Range tanggal |
| **Pencarian** | Cari di deskripsi |
| **Export** | Unduh log (opsional, Blok 6) |

### Aturan UX
- **Read-only** — Owner tidak bisa edit/hapus log
- **Pagination** — 50 per halaman
- **Detail per record** — klik → lihat snapshot lama vs baru
- **Highlight aksi sensitif** — koreksi, ubah setting, ubah role

### Aturan data
- **Tidak bisa dihapus** — audit permanen
- **Retensi** — 5 tahun (nanti kalau perlu bersih-bersih)
- **Hanya Owner & HRD** bisa akses

### Hasil
- Owner bisa audit kapan saja
- Tahu siapa melakukan apa

## 9. Tabel Baru: `attendance_monthly_summaries` (Opsional tapi Disarankan)

### Fungsi
Menyimpan **ringkasan bulanan** per karyawan — supaya dashboard & laporan cepat tanpa query berat.

### Kenapa perlu?
- Query agregat 1 bulan × 10 crew bisa berat kalau data banyak
- Rekap Bulanan butuh data ini
- Dashboard Owner butuh angka cepat

### Kolom inti
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | Karyawan |
| `bulan` | tinyint | 1–12 |
| `tahun` | smallint | 2026 |
| `total_hadir` | integer | Jumlah hari hadir |
| `total_terlambat` | integer | Jumlah hari terlambat |
| `total_menit_terlambat` | integer | Total menit |
| `total_disputed` | integer | Jumlah hari disputed |
| `total_koreksi` | integer | Jumlah koreksi |
| `total_jam_kerja` | decimal(8,2) | Total jam |
| `persentase_kehadiran` | decimal(5,2) | % |
| `generated_at` | datetime | Kapan dibuat |

### Aturan
- **Dibuat otomatis** saat generate Rekap Bulanan (cron tgl 1)
- **Bisa di-generate ulang** kalau ada koreksi susulan
- **Tidak diedit manual** — hasil hitung sistem
- **Unique** kombinasi `(employee_id, bulan, tahun)`

### Hasil
- Dashboard & laporan cepat
- Data historis tersimpan

## 10. Notifikasi di Blok 5

### Notifikasi yang muncul
| Event | Penerima | Kanal |
|---|---|---|
| Rekap Bulanan siap | Owner, HRD, Supervisor | In-app + email (attach PDF) |
| Ada absen disputed > 3 hari | Owner, HRD | In-app |
| Kontrak habis < 30 hari | Owner, HRD | In-app |
| Setting diubah | Owner (konfirmasi) | In-app |
| User baru dibuat | Owner | In-app |
| Role diubah | Owner | In-app |
| Login gagal berulang | Owner | In-app (keamanan) |

### Aturan
- **Owner dapat notifikasi ringkas** — jangan spam
- **Alert "perlu perhatian"** — berbasis aturan (mis. disputed lama)
- **Email Rekap Bulanan** — wajib
- **Notifikasi keamanan** — login gagal berulang, ubah role

### Hasil
- Owner tahu hal penting tanpa buka app terus
- Keamanan terpantau

## 11. Aturan "Perlu Perhatian" (Alert Rules)

### Fungsi
Dashboard Owner menampilkan **hal yang butuh perhatian** — bukan semua data.

### Aturan alert (contoh)
| Rule | Kondisi | Pesan |
|---|---|---|
| **Terlambat berulang** | Crew terlambat ≥ 3× dalam 7 hari | "Budi terlambat 3× minggu ini" |
| **Disputed lama** | Absen disputed > 3 hari belum selesai | "5 absen disputed > 3 hari" |
| **Kontrak habis** | Kontrak habis < 30 hari | "Kontrak Rina habis 15 hari lagi" |
| **Kehadiran rendah** | Kehadiran crew < 80% bulan ini | "Dodi kehadiran 75% bulan ini" |
| **Setting belum diisi** | Lokasi absen belum diatur | "Atur lokasi absen di Pengaturan" |

### Aturan
- **Maksimal 5 alert** di dashboard — jangan penuh
- **Bisa diklik** — arahkan ke halaman detail
- **Bisa di-dismiss** — Owner bisa tandai "sudah tahu"
- **Update real-time** — dihitung saat dashboard dibuka

### Hasil
- Owner fokus ke hal penting
- Tidak tenggelam di data

## 12. Keamanan Owner (Wajib)

| # | Aturan | Kenapa |
|---|---|---|
| 1 | **Hanya Owner** bisa akses Pengaturan | Permission `setting.manage` |
| 2 | **Hanya Owner** bisa kelola user | Permission `user.manage` |
| 3 | **Hanya Owner** bisa lihat activity log lengkap | Permission `audit.view` |
| 4 | **Owner tidak bisa hapus diri sendiri** | Cegah "mengunci diri" |
| 5 | **Minimal 1 Owner aktif** | Sistem tolak nonaktifkan Owner terakhir |
| 6 | **Ubah setting wajib konfirmasi** | Cegah salah klik |
| 7 | **Ubah role wajib konfirmasi** | Cegah salah assign |
| 8 | **Activity log tidak bisa dihapus** | Audit permanen |
| 9 | **Session Owner lebih pendek** | Auto-logout 30 menit idle |
| 10 | **2FA (opsional, Blok 6)** | Untuk keamanan ekstra |

### Kenapa session Owner lebih pendek
- Owner punya akses paling luas
- Kalau HP/laptop hilang, risiko besar
- Auto-logout 30 menit idle = aman

### Hasil
- Owner aman
- Tidak bisa "mengunci diri"

## 13. Kriteria Selesai Blok 5 (Definition of Done)

Blok 5 **selesai** kalau semua ini lolos:

| # | Kriteria | Cara Uji |
|---|---|---|
| 1 | Owner lihat dashboard setelah login | Login Owner → dashboard muncul |
| 2 | Dashboard menampilkan kartu statistik | Cek angka hadir, terlambat, disputed |
| 3 | Dashboard menampilkan grafik tren | Grafik 30 hari muncul |
| 4 | Dashboard menampilkan "Perlu Perhatian" | Alert muncul sesuai aturan |
| 5 | Owner bisa lihat laporan harian | Buka Laporan → pilih tanggal → preview |
| 6 | Owner bisa unduh PDF Rekap Final | Unduh → PDF terbuka |
| 7 | Owner bisa lihat laporan bulanan | Buka Laporan → pilih bulan → preview |
| 8 | Rekap Bulanan otomatis dibuat | Cron tgl 1 → PDF muncul |
| 9 | Rekap Bulanan terkirim ke Owner | Cek inbox → email dengan attach PDF |
| 10 | Rekap Bulanan terkirim ke HRD & Supervisor | Cek inbox mereka |
| 11 | Owner bisa ubah setting absensi | Buka Pengaturan → ubah radius → simpan |
| 12 | Validasi setting ketat | Coba isi radius negatif → ditolak |
| 13 | Ubah setting tercatat di log | Cek activity log → ada record |
| 14 | Owner bisa kelola user | Buka Kelola User → tambah user → assign role |
| 15 | Owner bisa assign multi-role | Assign 2 role ke 1 user → tersimpan |
| 16 | Owner tidak bisa nonaktifkan diri sendiri | Coba → ditolak |
| 17 | Owner tidak bisa nonaktifkan Owner terakhir | Coba → ditolak |
| 18 | Owner bisa lihat activity log | Buka log → filter → data muncul |
| 19 | Alert "perlu perhatian" muncul | Buat kondisi (mis. disputed lama) → alert muncul |
| 20 | Notifikasi Rekap Bulanan muncul | Cek bell → notifikasi ada |
| 21 | Tampilan jalan di HP | Uji Owner dari Android/iPhone |
| 22 | Session Owner auto-logout | Diamkan 30 menit → logout |

**Kalau 22 poin ini lolos → Blok 5 selesai. Siklus absensi lengkap end-to-end.**

## 14. Yang TIDAK Dibuat di Blok 5

| ❌ Jangan | Alasan |
|---|---|
| Fitur Cuti & Izin | Blok 6 |
| Fitur Slip Gaji | Blok 6 |
| Fitur Jadwal Shift | Blok 6 |
| Broadcast / Pengumuman | Blok 6 |
| Koreksi self-service (crew ajukan) | Blok 6 |
| Issue Report | Blok 6 |
| Push notification PWA | Blok 6 |
| WhatsApp notifikasi | Opsional nanti |
| 2FA | Blok 6 (opsional) |
| Multi-outlet | Nanti kalau ada cabang |
| Dashboard keuangan detail | Blok 6 (setelah gaji) |
| Export Excel | Blok 6 (opsional) |
| Grafik kompleks (heatmap, dll) | Nanti kalau perlu |

## 15. Urutan Pengerjaan di Blok 5

| Langkah | Yang Dikerjakan | Hasil |
|---|---|---|
| 1 | Migration `attendance_monthly_summaries` (opsional) | Tabel siap |
| 2 | Service agregasi data dashboard | Query siap |
| 3 | Halaman Dashboard Owner (kartu + grafik) | Dashboard jalan |
| 4 | Aturan "Perlu Perhatian" (alert) | Alert jalan |
| 5 | Halaman Laporan (harian, bulanan, per karyawan) | Laporan jalan |
| 6 | Service generate PDF Rekap Bulanan | PDF jadi |
| 7 | Cron tgl 1 generate Rekap Bulanan | Otomatis |
| 8 | Distribusi email Rekap Bulanan | Email terkirim |
| 9 | Halaman Pengaturan (tab + form + validasi) | Setting jalan |
| 10 | Preview peta untuk lat/lng | Owner yakin |
| 11 | Halaman Kelola User (perluas dari Blok 1) | Multi-role jalan |
| 12 | Halaman Activity Log (filter + pagination) | Audit jalan |
| 13 | Middleware + permission cek | Keamanan |
| 14 | Auto-logout Owner 30 menit | Keamanan |
| 15 | Notifikasi (Rekap Bulanan, alert, keamanan) | Komunikasi jalan |
| 16 | Cache dashboard & laporan | Performa |
| 17 | Uji 22 kriteria selesai | Blok 5 done |

## 16. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| **Dashboard berat** | Cache 5 menit + query agregat + index database |
| **Rekap Bulanan gagal** | Cron retry + log error + notifikasi Owner |
| **Email Rekap Bulanan masuk spam** | SMTP kredibel + SPF/DKIM |
| **Owner salah ubah setting** | Konfirmasi + preview peta + activity log |
| **Owner nonaktifkan diri sendiri** | Sistem tolak + minimal 1 Owner aktif |
| **Owner lupa password** | Reset via email (sudah ada Blok 1) |
| **Activity log penuh** | Pagination + retensi 5 tahun |
| **Data bulanan tidak akurat** | Hanya data `final` + regenerate kalau ada koreksi |
| **Grafik tidak muncul** | Fallback tabel kalau library grafik gagal |
| **Owner akses dari HP hilang** | Session 30 menit + 2FA (Blok 6) |

## 17. Ringkasan Blok 5

| Aspek | Isi |
|---|---|
| **Tujuan** | Owner lihat hasil, atur aplikasi, terima Rekap Bulanan |
| **Tabel baru** | `attendance_monthly_summaries` (opsional) |
| **Halaman** | Dashboard, Laporan, Pengaturan, Kelola User, Activity Log |
| **PDF baru** | Rekap Bulanan |
| **Cron** | Tgl 1 generate Rekap Bulanan |
| **Email** | Rekap Bulanan ke Owner, HRD, Supervisor |
| **Notifikasi** | Rekap Bulanan, alert "perlu perhatian", keamanan |
| **Keamanan** | Owner tidak bisa nonaktifkan diri, minimal 1 Owner, session pendek, activity log |
| **Kriteria selesai** | 22 poin |
| **Yang belum** | Cuti, gaji, jadwal, broadcast, koreksi self-service (Blok 6) |

---

## 🎯 Setelah Blok 5 Selesai

**Siklus absensi lengkap end-to-end:**

```
Crew absen (pending)
   ↓
Supervisor validasi (validated / disputed)
   ↓
PDF Rekap Harian → HRD
   ↓
HRD verifikasi (verified / corrected / disputed)
   ↓
PDF Rekap Final → Owner
PDF Berita Acara → Crew (kalau koreksi)
   ↓
Absen final & terkunci
   ↓
Owner lihat dashboard, terima Rekap Bulanan
```

**Aplikasi sudah bisa dipakai produksi untuk modul absensi.**

**Yang belum (Blok 6 — fitur HRD lainnya):**
- ❌ Cuti & Izin
- ❌ Slip Gaji
- ❌ Jadwal Shift
- ❌ Broadcast / Pengumuman
- ❌ Koreksi self-service (crew ajukan)
- ❌ Issue Report
- ❌ Push notification PWA
- ❌ 2FA (opsional)
- ❌ Export Excel (opsional)

---

## 📊 Status Keseluruhan Proyek

| Blok | Modul | Status |
|---|---|---|
| **1** | Fondasi (Auth, Role, Employee, Setting) | ✅ Selesai |
| **2** | Crew: Absensi | ✅ Selesai |
| **3** | Supervisor: Validasi + PDF Harian | ✅ Selesai |
| **4** | HRD: Verifikasi + PDF Final + Berita Acara | ✅ Selesai |
| **5** | Owner: Dashboard + Pengaturan + Rekap Bulanan | ✅ Selesai |
| **6** | Fitur lain (Cuti, Gaji, Jadwal, dll) | ⏳ Belum |

**Modul Absensi = 100% selesai setelah Blok 5.**

---

Mau saya lanjutkan ke **Blok 6 — Fitur HRD Lainnya (Cuti, Gaji, Jadwal, Broadcast, Koreksi Self-Service, Issue Report)**? Atau Anda mau saya rangkum dulu **keseluruhan blueprint** (Blok 1–5) dalam 1 dokumen ringkas?