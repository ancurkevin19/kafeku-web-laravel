# 📘 BLOK 2 — Crew: Absensi (Detail Teknis, Tanpa Koding)

Tujuan Blok 2: **Aplikasi dapat melakukan absensi karyawan (masuk/pulang) dengan selfie dan GPS, validasi oleh supervisor, serta koreksi oleh HRD.**

---

## 1. Ringkasan Fitur Absensi

| Fitur | Deskripsi | Siapa yang akses |
|---|---|---|
| Absensi Masuk | Karyawan mencatat waktu masuk dengan lokasi + selfie | Crew |
| Absensi Pulang | Karyawan mencatat waktu pulang dengan lokasi + selfie | Crew |
| Cek Absensi Harian | Melihat riwayat absensi sendiri | Crew |
| Validasi Absensi | Supervisor menyetujui/tolak absensi timnya | Supervisor |
| Verifikasi Absensi | HRD memverifikasi absensi yang sudah divalidasi supervisor | HRD |
| Koreksi Absensi | HRD dapat mengubah data absensi (jam, lokasi, keterangan) | HRD |
| Laporan Absensi | Melihat rekap absensi per karyawan/tanggal | Supervisor, HRD, Owner |

---

## 2. Tabel Database

### 2.1 Tabel `attendances`

Menyimpan **record absensi harian** setiap karyawan.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | Primary key |
| `employee_id` | bigint | FK ke tabel `employees` |
| `tanggal` | date | Tanggal absensi |
| `jam_masuk` | time nullable | Waktu absensi masuk |
| `jam_pulang` | time nullable | Waktu absensi pulang |
| `status_masuk` | enum | `tepat_waktu`, `terlambat`, `belum_absen` |
| `status_pulang` | enum | `tepat_waktu`, `awal_pulang`, `belum_absen` |
| `lokasi_masuk_lat` | string nullable | Latitude saat absensi masuk |
| `lokasi_masuk_lng` | string nullable | Longitude saat absensi masuk |
| `lokasi_pulang_lat` | string nullable | Latitude saat absensi pulang |
| `lokasi_pulang_lng` | string nullable | Longitude saat absensi pulang |
| `jarak_masuk_meter` | integer nullable | Jarak dari kantor saat masuk |
| `jarak_pulang_meter` | integer nullable | Jarak dari kantor saat pulang |
| `foto_masuk` | string nullable | Path foto selfie saat masuk |
| `foto_pulang` | string nullable | Path foto selfie saat pulang |
| `keterangan` | text nullable | Catatan tambahan (mis. cuti, izin) |
| `validated_by` | bigint nullable | ID user supervisor yang memvalidasi |
| `validated_at` | datetime nullable | Waktu validasi |
| `verification_by` | bigint nullable | ID user HRD yang memverifikasi |
| `verification_at` | datetime nullable | Waktu verifikasi |
| `status_validasi` | enum | `pending`, `disetujui`, `ditolak` |
| `status_verifikasi` | enum | `belum`, `diverifikasi`, `ditolak` |
| `is_corrected` | boolean | Apakah absensi pernah dikoreksi |
| `corrected_by` | bigint nullable | Siapa yang terakhir koreksi |
| `corrected_at` | datetime nullable | Waktu koreksi |
| `created_at`, `updated_at` | timestamp | Bawaan Laravel |

### Aturan
- **1 karyawan = 1 absensi per tanggal** (unique constraint pada `employee_id` + `tanggal`)
- Soft delete untuk audit (karyawan resign, absensi tetap ada)
- `status_masuk` otomatis dihitung berdasarkan `jam_masuk` vs `jam_masuk` dari setting

### 2.2 Tabel `attendance_corrections` (Riwayat Koreksi)

Menyimpan **log perubahan** saat HRD mengoreksi absensi.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `attendance_id` | bigint | FK ke `attendances` |
| `field` | string | Kolom yang diubah (mis. `jam_masuk`, `lokasi_masuk_lat`) |
| `nilai_lama` | text nullable | Nilai sebelum diubah |
| `nilai_baru` | text nullable | Nilai setelah diubah |
| `alasan` | text | Alasan koreksi |
| `corrected_by` | bigint | Siapa yang melakukan koreksi |
| `created_at` | timestamp | — |

---

## 3. Permission Absensi (Spatie)

Permission baru yang ditambahkan di Blok 2:

| Permission | Deskripsi |
|---|---|
| `attendance.view.self` | Lihat absensi sendiri |
| `attendance.view.all` | Lihat absensi semua karyawan |
| `attendance.create` | Buat absensi masuk/pulang |
| `attendance.validate` | Validasi absensi tim (supervisor) |
| `attendance.verify` | Verifikasi absensi (HRD) |
| `attendance.correct` | Koreksi data absensi (HRD) |

### Mapping Role → Permission (update dari Blok 1)

| Permission | Crew | Supervisor | HRD | Owner |
|---|:---:|:---:|:---:|:---:|
| `attendance.view.self` | ✅ | ✅ | ✅ | ✅ |
| `attendance.view.all` | ❌ | ✅ | ✅ | ✅ |
| `attendance.create` | ✅ | ❌ | ❌ | ❌ |
| `attendance.validate` | ❌ | ✅ | ❌ | ❌ |
| `attendance.verify` | ❌ | ❌ | ✅ | ❌ |
| `attendance.correct` | ❌ | ❌ | ✅ | ❌ |

---

## 4. Update Tabel `settings` (Blok 1 + Blok 2)

Setting baru untuk absensi yang ditambahkan:

| Key | Tipe | Contoh Nilai | Grup |
|---|---|---|---|
| `office_lat` | string | `-6.1234567` | absensi |
| `office_lng` | string | `106.1234567` | absensi |
| `office_radius_meter` | integer | `100` | absensi |
| `jam_masuk` | string | `08:00` | absensi |
| `jam_pulang` | string | `17:00` | absensi |
| `toleransi_terlambat_menit` | integer | `10` | absensi |
| `wajib_selfie` | boolean | `true` | absensi |
| `wajib_gps` | boolean | `true` | absensi |

Setting ini sudah didefinisikan di Blok 1, diisi via seeder, dan digunakan di Blok 2 untuk validasi absensi.

---

## 5. Middleware & Akses

| Route | Permission Diperlukan | Akses |
|---|---|---|
| `absensi/masuk` | `attendance.create` | Crew |
| `absensi/pulang` | `attendance.create` | Crew |
| `absensi/saya` | `attendance.view.self` | Crew |
| `absensi/tim` | `attendance.view.all` + `attendance.validate` | Supervisor |
| `absensi/semua` | `attendance.view.all` | HRD, Owner |
| `absensi/validasi` | `attendance.validate` | Supervisor |
| `absensi/verifikasi` | `attendance.verify` | HRD |
| `absensi/koreksi` | `attendance.correct` | HRD |

- Tanpa permission → **403 Forbidden**
- Middleware `permission:` dari Spatie digunakan di route

---

## 6. Flow Absensi

### 6.1 Flow Absensi Masuk (Crew)

1. Karyawan buka halaman "Absensi Masuk"
2. Sistem cek apakah sudah absen hari ini (`employee_id` + `tanggal`)
3. Kalau sudah absen → tampilkan "Anda sudah absen masuk hari ini"
4. Kalau belum:
   - Ambil lokasi GPS (jika `wajib_gps = true`)
   - Ambil selfie kamera (jika `wajib_selfie = true`)
   - Cek jarak dari `office_lat`/`office_lng` vs lokasi user
   - Kalau jarak > `office_radius_meter` → tolak, tampilkan pesan "Di luar radius kantor"
   - Simpan ke tabel `attendances` dengan `status_masuk = pending`
5. Redirect ke halaman "Absensi Saya" dengan notifikasi sukses

### 6.2 Flow Absensi Pulang (Crew)

1. Karyawan buka halaman "Absensi Pulang"
2. Sistem cek apakah sudah absen masuk hari ini
3. Kalau belum absen masuk → tolak, tampilkan "Anda belum absen masuk hari ini"
4. Kalau sudah absen masuk tapi belum absen pulang:
   - Ambil lokasi GPS + selfie
   - Simpan `jam_pulang`, `lokasi_pulang`, `foto_pulang`
   - Hitung `status_pulang` berdasarkan `jam_pulang`
5. Redirect ke halaman "Absensi Saya"

### 6.3 Flow Validasi (Supervisor)

1. Supervisor buka halaman "Validasi Absensi Tim"
2. Tampilkan daftar absensi karyawan dalam timnya dengan status `pending`
3. Pilih absensi → klik "Setujui" atau "Tolak"
4. Simpan `status_validasi` + `validated_by` + `validated_at`
5. Jika ditolak → absensi tetap ada tapi tidak diverifikasi

### 6.4 Flow Verifikasi (HRD)

1. HRD buka halaman "Verifikasi Absensi"
2. Tampilkan daftar absensi yang sudah `disetujui` supervisor
3. Pilih absensi → klik "Diverifikasi" atau "Ditolak"
4. Simpan `status_verifikasi` + `verification_by` + `verification_at`

### 6.5 Flow Koreksi (HRD)

1. HRD buka halaman "Koreksi Absensi"
2. Pilih absensi yang ingin dikoreksi
3. Ubah jam, lokasi, atau keterangan
4. Masukkan alasan koreksi
5. Simpan ke `attendance_corrections` dan update `attendances`
6. Set `is_corrected = true`

---

## 7. Layout & Sidebar (Update dari Blok 1)

Menu baru yang muncul di sidebar Blok 2:

| Role | Menu Baru (Blok 2) |
|---|---|
| **Owner** | Dashboard, Karyawan, Absensi (Semua), Pengaturan, Kelola User, Activity Log |
| **HRD** | Dashboard, Karyawan, Absensi (Semua), Verifikasi, Koreksi, Activity Log |
| **Supervisor** | Dashboard, Tim Saya, Absensi Tim, Validasi Absensi |
| **Crew** | Dashboard, Profil Saya, Absensi Masuk, Absensi Pulang, Absensi Saya |

> Menu absensi ditambahkan di Blok 2. Menu cuti, gaji, jadwal shift masih belum muncul (blok berikutnya).

---

## 8. Halaman yang Harus Jadi di Blok 2

| # | Halaman | Role | Fungsi |
|---|---|---|---|
| 1 | **Absensi Masuk** | Crew | Absensi masuk dengan selfie + GPS |
| 2 | **Absensi Pulang** | Crew | Absensi pulang dengan selfie + GPS |
| 3 | **Absensi Saya** | Crew | Lihat riwayat absensi sendiri |
| 4 | **Absensi Tim** | Supervisor | Lihat absensi karyawan dalam tim |
| 5 | **Validasi Absensi** | Supervisor | Setujui/tolak absensi tim |
| 6 | **Absensi Semua** | HRD, Owner | Lihat absensi semua karyawan |
| 7 | **Verifikasi Absensi** | HRD | Verifikasi absensi yang sudah disetujui supervisor |
| 8 | **Koreksi Absensi** | HRD | Ubah data absensi + catat alasan |

### Aturan
- Halaman **Absensi Masuk/Pulang** harus menampilkan kamera (selfie) dan peta (lokasi) — bisa pakai HTML5 Geolocation API + Camera API
- Validasi radius pakai formula Haversine (hitung jarak dua titik lat/lng)
- `status_masuk` otomatis: tepat waktu jika <= `jam_masuk` + toleransi, terlambat jika melebihi
- Absensi hanya bisa dilakukan dalam hari kerja (Senin-Jumat, bisa diatur via setting nanti)
- Tidak bisa absen masuk dua kali dalam satu hari

---

## 9. Activity Log (Update)

Aksi baru yang dicatat di `activity_logs`:

| Aksi | Model | Deskripsi |
|---|---|---|
| `attendance_create` | `attendances` | Karyawan absen masuk/pulang |
| `attendance_validate` | `attendances` | Supervisor setujui/tolak absensi |
| `attendance_verify` | `attendances` | HRD verifikasi absensi |
| `attendance_correct` | `attendances` | HRD koreksi data absensi |

Data lama & data baru disimpan dalam JSON untuk audit lengkap.

---

## 10. Seeder Bootstrap (Update Blok 2)

Tambahan data yang di-seed:

| Urutan | Data | Kenapa |
|---|---|---|
| 8 | **Permissions absensi** | `attendance.view.self`, `attendance.view.all`, `attendance.create`, `attendance.validate`, `attendance.verify`, `attendance.correct` |
| 9 | **Mapping role → permission absensi** | Hubungkan role dengan permission absensi |
| 10 | **Settings absensi default** | `office_lat`, `office_lng`, `office_radius_meter`, `jam_masuk`, `jam_pulang`, `toleransi_terlambat_menit`, `wajib_selfie`, `wajib_gps` |

---

## 11. Kriteria Selesai Blok 2 (Definition of Done)

| # | Kriteria | Cara Uji |
|---|---|---|
| 1 | Crew bisa absen masuk | Login sebagai Crew → buka Absensi Masuk → selfie + GPS → simpan → muncul di Absensi Saya |
| 2 | Crew tidak bisa absen masuk 2x | Coba absen masuk lagi hari yang sama → sistem tolak |
| 3 | Crew di luar radius kantor tidak bisa absen | Simulasikan lokasi jauh → sistem menolak dengan pesan "Di luar radius" |
| 4 | Crew bisa absen pulang | Setelah absen masuk → buka Absensi Pulang → selfie + GPS → simpan |
| 5 | Supervisor bisa validasi absensi tim | Login Supervisor → buka Validasi Absensi → setujui/tolak absensi |
| 6 | HRD bisa verifikasi absensi | Login HRD → buka Verifikasi Absensi → verifikasi absensi yang sudah disetujui supervisor |
| 7 | HRD bisa koreksi absensi | Login HRD → buka Koreksi Absensi → ubah jam → simpan → muncul di activity log |
| 8 | Activity log mencatat absensi | Setelah absen → cek Activity Log → ada record `attendance_create` |
| 9 | Sidebar beda per role | Login Crew → lihat menu absensi sendiri; Login Supervisor → lihat menu validasi tim |
| 10 | Tidak punya permission → 403 | Akses URL `/absensi/validasi` sebagai Crew → 403 Forbidden |
| 11 | Tampilan jalan di HP | Buka di Android/iPhone → kamera selfie dan lokasi berfungsi |
| 12 | Status otomatis tepat waktu/terlambat | Absen setelah jam_masuk + toleransi → status otomatis `terlambat` |

**Kalau 12 poin ini lolos → Blok 2 selesai, lanjut Blok 3 (PDF & Laporan).**

---

## 12. Yang TIDAK Dibuat di Blok 2

| ❌ Jangan | Alasan |
|---|---|
| Cuti & Izin | Blok 6 |
| Slip Gaji | Blok 6 |
| Jadwal Shift | Blok 6 |
| Notifikasi push/WA | Blok 4 |
| Dashboard statistik absensi | Blok 5 |
| PDF laporan absensi | Blok 3 |
| Multi-shift (pagi/siang/malam) | Bisa ditunda ke Blok 6 |
| Absensi via QR Code | Bisa ditunda ke Blok 4 |
| Integrasi fingerprint | Perlu hardware eksternal |

---

## 13. Urutan Pengerjaan di Blok 2

| Langkah | Yang Dikerjakan | Hasil |
|---|---|---|
| 1 | Migration `attendances` + `attendance_corrections` | Tabel absensi ada |
| 2 | Seeder: permissions absensi + mapping | RBAC absensi terisi |
| 3 | Seeder: settings absensi default | Setting absensi siap |
| 4 | Middleware permission absensi | Akses terkontrol |
| 5 | Update sidebar dinamis | Menu absensi muncul per role |
| 6 | Halaman Absensi Masuk (Crew) | Crew bisa absen masuk |
| 7 | Halaman Absensi Pulang (Crew) | Crew bisa absen pulang |
| 8 | Halaman Absensi Saya (Crew) | Lihat riwayat sendiri |
| 9 | Halaman Absensi Tim + Validasi (Supervisor) | Supervisor bisa validasi |
| 10 | Halaman Absensi Semua + Verifikasi (HRD) | HRD bisa verifikasi |
| 11 | Halaman Koreksi Absensi (HRD) | HRD bisa koreksi |
| 12 | Activity log otomatis untuk absensi | Audit jalan |
| 13 | Uji 12 kriteria selesai | Blok 2 done |

---

## Ringkasan Blok 2

| Aspek | Isi |
|---|---|
| **Tujuan** | Absensi karyawan (masuk/pulang) dengan selfie + GPS, validasi, verifikasi, koreksi |
| **Tabel baru** | `attendances`, `attendance_corrections` |
| **Permission baru** | `attendance.view.self`, `attendance.view.all`, `attendance.create`, `attendance.validate`, `attendance.verify`, `attendance.correct` |
| **Setting baru** | `office_lat`, `office_lng`, `office_radius_meter`, `jam_masuk`, `jam_pulang`, `toleransi_terlambat_menit`, `wajib_selfie`, `wajib_gps` |
| **Halaman** | Absensi Masuk, Absensi Pulang, Absensi Saya, Absensi Tim, Validasi Absensi, Absensi Semua, Verifikasi Absensi, Koreksi Absensi |
| **Role akses** | Crew (masuk/pulang/saya), Supervisor (tim/validasi), HRD (semua/verifikasi/koreksi), Owner (semua) |
| **Kriteria selesai** | 12 poin (absensi, radius, validasi, verifikasi, koreksi, 403, log, mobile) |
| **Yang belum** | Cuti, gaji, jadwal shift, notifikasi, PDF, dashboard statistik |

---

Mau saya lanjutkan ke **Blok 3 — PDF & Laporan** dengan detail teknis yang sama (tanpa koding)?