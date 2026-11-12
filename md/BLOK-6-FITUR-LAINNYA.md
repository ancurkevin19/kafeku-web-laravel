# 📘 BLOK 6 — Fitur HRD Lainnya (Detail Teknis, Tanpa Koding)

Tujuan Blok 6: **Melengkapi modul HRD selain absensi** — cuti, gaji, jadwal, broadcast, koreksi self-service, dan issue report. Ini blok **terbesar** karena banyak fitur. Saya pecah jadi **6 sub-blok** dengan prioritas jelas, supaya bisa dikerjakan satu per satu.

---

## 1. Prasyarat

| Prasyarat | Status |
|---|---|
| Blok 1–5 selesai (absensi lengkap end-to-end) | ✅ |
| Auth, role, permission, layout, activity log | ✅ |
| PDF engine (DomPDF), email (SMTP), notifikasi in-app | ✅ |
| Tabel `users`, `employees`, `attendances`, `attendance_reports` | ✅ |

Kalau Blok 5 belum selesai, **jangan lanjut** — Blok 6 butuh semua fondasi.

---

## 2. Prioritas Fitur (Urutan Pengerjaan)

| Sub-Blok | Fitur | Prioritas | Alasan |
|---|---|---|---|
| **6A** | Cuti & Izin | 🔴 Tinggi | Paling sering dipakai crew |
| **6B** | Slip Gaji | 🔴 Tinggi | Crew pasti butuh, HRD butuh |
| **6C** | Jadwal Shift | 🟡 Sedang | Pertanyaan paling umum crew |
| **6D** | Broadcast / Pengumuman | 🟢 Rendah | Murah, cepat, banyak manfaat |
| **6E** | Koreksi Self-Service | 🟡 Sedang | Crew bisa ajukan sendiri |
| **6F** | Issue Report | 🟢 Rendah | Nice to have |

**Aturan:** selesaikan **satu sub-blok end-to-end** sebelum lanjut. Jangan campur.

---

# 🟥 SUB-BLOK 6A — Cuti & Izin

## 6A.1 Konsep
Crew ajukan cuti/izin/sakit → Supervisor approve → HRD catat → saldo berkurang. Owner approve kalau yang ajukan Supervisor/HRD.

## 6A.2 Tabel Baru

### `leave_types`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `nama` | string | Cuti Tahunan, Izin, Sakit, Cuti Khusus |
| `kuota_default` | integer | Hari per tahun (0 = tidak berkuota) |
| `perlu_bukti` | boolean | Sakit perlu surat dokter? |
| `berbayar` | boolean | Gaji tetap dibayar? |
| `aktif` | boolean | — |

### `leave_balances`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | — |
| `leave_type_id` | bigint FK | — |
| `tahun` | smallint | 2026 |
| `kuota` | integer | Total jatah |
| `terpakai` | integer | Sudah dipakai |
| `sisa` | integer | kuota − terpakai |
| `updated_at` | timestamp | — |

**Unique:** `(employee_id, leave_type_id, tahun)`

### `leaves`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | Pengaju |
| `leave_type_id` | bigint FK | Jenis |
| `tanggal_mulai` | date | — |
| `tanggal_selesai` | date | — |
| `jumlah_hari` | integer | Dihitung sistem |
| `alasan` | text | — |
| `bukti_path` | string nullable | Surat dokter, dll |
| `status` | enum | `pending_supervisor`, `pending_hrd`, `approved`, `rejected`, `cancelled` |
| `approved_by_supervisor` | bigint nullable | — |
| `approved_at_supervisor` | datetime nullable | — |
| `approved_by_hrd` | bigint nullable | — |
| `approved_at_hrd` | datetime nullable | — |
| `catatan_approver` | text nullable | — |
| `pdf_path` | string nullable | Surat cuti final |
| `nomor_surat` | string nullable | `KFK-CT-2026-11-001` |
| `created_at` | timestamp | — |

## 6A.3 Alur Status
```
pending_supervisor → pending_hrd → approved
       │                  │
       └──► rejected ◄────┘
              │
              └──► cancelled (oleh pengaju, sebelum approved)
```

## 6A.4 Halaman

### Crew
- **"Cuti Saya"** — saldo cuti, riwayat, tombol ajukan
- **"Ajukan Cuti"** — form: jenis, tanggal, alasan, upload bukti
- **"Detail Cuti"** — status, riwayat approval, unduh surat

### Supervisor
- **"Approval Cuti Tim"** — daftar pending, detail, approve/reject

### HRD
- **"Kelola Cuti"** — semua cuti, approve (untuk supervisor), atur saldo
- **"Saldo Cuti"** — per karyawan, adjust manual (dengan alasan)

### Owner
- **"Cuti HRD/Supervisor"** — approve cuti dari role atas
- **"Laporan Cuti"** — rekap cuti per bulan/tahun

## 6A.5 PDF
- **Surat Cuti Final** (`KFK-CT-YYYY-MM-XXX.pdf`) — setelah approved, dikirim ke crew + HRD
- Isi: data karyawan, jenis, tanggal, jumlah hari, alasan, tanda tangan approver

## 6A.6 Aturan Bisnis
- **Cek saldo** sebelum approve — kalau sisa < jumlah hari, tolak
- **Tidak bisa cuti di hari yang sudah ada absen** — validasi
- **Cuti sakit** wajib upload surat dokter
- **Approval berjenjang** — Crew → Supervisor → HRD; Supervisor → HRD; HRD → Owner
- **Saldo berkurang** saat status `approved` (bukan saat pengajuan)
- **Batal cuti** — hanya sebelum `approved`, saldo tidak berkurang
- **Kalau cuti disetujui**, hari itu otomatis ditandai "izin" di rekap absensi

## 6A.7 Kriteria Selesai 6A
| # | Kriteria |
|---|---|
| 1 | Crew bisa ajukan cuti |
| 2 | Saldo dicek otomatis |
| 3 | Supervisor bisa approve/reject |
| 4 | HRD bisa approve (untuk supervisor) |
| 5 | Owner bisa approve (untuk HRD) |
| 6 | Saldo berkurang setelah approved |
| 7 | Surat Cuti PDF terkirim ke crew |
| 8 | Rekap absensi menandai hari cuti |
| 9 | Notifikasi di setiap tahap |
| 10 | Activity log tercatat |

---

# 🟥 SUB-BLOK 6B — Slip Gaji

## 6B.1 Konsep
HRD buat komponen gaji → generate slip bulanan → Owner approve → kirim ke crew. Perhitungan otomatis dari absensi `final` + input manual HRD.

## 6B.2 Tabel Baru

### `salary_components` (master komponen)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `nama` | string | Gaji Pokok, Tunjangan Transport, BPJS, Potongan Telat |
| `tipe` | enum | `pendapatan`, `potongan` |
| `nominal_default` | decimal(12,2) | Nilai default |
| `otomatis` | boolean | Hitung dari absensi? |
| `formula` | string nullable | Rumus (mis. `menit_terlambat * 500`) |
| `aktif` | boolean | — |

### `employee_salaries` (gaji per karyawan)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | — |
| `bulan` | tinyint | 1–12 |
| `tahun` | smallint | — |
| `gaji_pokok` | decimal(12,2) | — |
| `total_pendapatan` | decimal(12,2) | — |
| `total_potongan` | decimal(12,2) | — |
| `total_diterima` | decimal(12,2) | — |
| `status` | enum | `draft`, `pending_owner`, `approved`, `published` |
| `approved_by` | bigint nullable | — |
| `approved_at` | datetime nullable | — |
| `pdf_path` | string nullable | — |
| `nomor_dokumen` | string nullable | `KFK-SLP-2026-11-001` |

**Unique:** `(employee_id, bulan, tahun)`

### `salary_details` (rincian per komponen)
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_salary_id` | bigint FK | — |
| `salary_component_id` | bigint FK | — |
| `nama` | string | Snapshot nama |
| `tipe` | enum | pendapatan/potongan |
| `nominal` | decimal(12,2) | — |
| `keterangan` | text nullable | — |

## 6B.3 Alur Status
```
draft → pending_owner → approved → published
                             │
                             └──► rejected (kembali ke draft)
```

## 6B.4 Halaman

### Crew
- **"Slip Gaji Saya"** — list per bulan, unduh PDF
- **"Detail Slip"** — rincian pendapatan & potongan

### HRD
- **"Payroll"** — pilih bulan, lihat semua crew, generate massal
- **"Detail Payroll per Crew"** — edit manual komponen, kalkulasi ulang
- **"Review & Ajukan ke Owner"** — kirim semua slip ke Owner untuk approve

### Owner
- **"Approval Payroll"** — review total, approve/reject
- **"Laporan Payroll"** — total biaya gaji per bulan

## 6B.5 PDF
- **Slip Gaji** (`KFK-SLP-YYYY-MM-XXX.pdf`) — 1 per crew per bulan
- Isi: data karyawan, periode, rincian pendapatan, rincian potongan, total diterima, nomor dokumen, catatan
- **Watermark nama penerima** — cegah penyebaran

## 6B.6 Aturan Bisnis
- **Hanya data absensi `final`** yang dipakai untuk hitung potongan/lembur
- **Potongan telat** = `menit_terlambat × tarif_per_menit` (dari komponen)
- **Gaji pokok** diisi per karyawan (bisa beda-beda)
- **HRD tidak bisa approve sendiri** — Owner yang approve
- **Slip yang sudah `published` tidak bisa diubah** — koreksi via slip baru
- **PDF password** (opsional) — dikirim terpisah
- **Retensi** — 5 tahun

## 6B.7 Kriteria Selesai 6B
| # | Kriteria |
|---|---|
| 1 | HRD bisa setup komponen gaji |
| 2 | HRD bisa set gaji pokok per karyawan |
| 3 | Generate slip massal 1 bulan |
| 4 | Kalkulasi otomatis dari absensi |
| 5 | HRD bisa edit manual komponen |
| 6 | Owner bisa approve/reject |
| 7 | PDF slip terkirim ke crew |
| 8 | Crew bisa unduh slip |
| 9 | Slip published terkunci |
| 10 | Activity log tercatat |

---

# 🟨 SUB-BLOK 6C — Jadwal Shift

## 6C.1 Konsep
Supervisor atur jadwal mingguan → crew lihat di HP → crew bisa ajukan tukar shift → supervisor approve.

## 6C.2 Tabel Baru

### `shift_templates`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `nama` | string | Pagi, Siang, Malam |
| `jam_mulai` | time | 08:00 |
| `jam_selesai` | time | 16:00 |
| `aktif` | boolean | — |

### `schedules`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | — |
| `tanggal` | date | — |
| `shift_template_id` | bigint FK | — |
| `status` | enum | `scheduled`, `swap_requested`, `swapped`, `cancelled` |
| `catatan` | text nullable | — |
| `created_by` | bigint FK → users | Supervisor |

**Unique:** `(employee_id, tanggal)`

### `shift_swap_requests`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `requester_id` | bigint FK → employees | Yang minta tukar |
| `target_id` | bigint FK → employees | Yang diajak tukar |
| `schedule_requester_id` | bigint FK → schedules | — |
| `schedule_target_id` | bigint FK → schedules | — |
| `alasan` | text | — |
| `status` | enum | `pending_target`, `pending_supervisor`, `approved`, `rejected` |
| `approved_by` | bigint nullable | — |
| `created_at` | timestamp | — |

## 6C.3 Halaman

### Crew
- **"Jadwal Saya"** — kalender mingguan
- **"Ajukan Tukar Shift"** — pilih shift sendiri + target
- **"Riwayat Tukar"** — status permintaan

### Supervisor
- **"Atur Jadwal"** — drag & drop crew ke shift per hari
- **"Approval Tukar Shift"** — approve/reject
- **"Cetak Jadwal Mingguan"** — PDF

### HRD
- **"Lihat Jadwal Semua"** — read-only
- **"Rekap Jam Kerja"** — dari jadwal + absensi

## 6C.4 PDF
- **Jadwal Shift Mingguan** — tabel crew × hari, dikirim ke crew + HRD tiap minggu
- **Surat Tukar Shift** (opsional) — kalau ada tukar disetujui

## 6C.5 Aturan Bisnis
- **1 crew 1 shift per hari** — unique constraint
- **Jadwal dibuat H-7** — supervisor atur minggu depan
- **Tukar shift butuh persetujuan target dulu**, baru supervisor
- **Crew tidak bisa ubah jadwal sendiri** — hanya ajukan tukar
- **Jadwal jadi acuan** untuk validasi absensi (kalau crew absen di luar jadwal, tandai)

## 6C.6 Kriteria Selesai 6C
| # | Kriteria |
|---|---|
| 1 | Supervisor bisa atur jadwal |
| 2 | Crew lihat jadwal di HP |
| 3 | Crew bisa ajukan tukar |
| 4 | Target bisa terima/tolak |
| 5 | Supervisor bisa approve tukar |
| 6 | PDF jadwal mingguan terkirim |
| 7 | Notifikasi di setiap tahap |
| 8 | Activity log tercatat |

---

# 🟩 SUB-BLOK 6D — Broadcast / Pengumuman

## 6D.1 Konsep
Owner/HRD/Supervisor kirim **pengumuman** ke role tertentu — muncul di dashboard crew.

## 6D.2 Tabel Baru

### `announcements`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `judul` | string | — |
| `isi` | text | — |
| `target_roles` | json | `["crew","supervisor"]` |
| `prioritas` | enum | `info`, `penting`, `urgent` |
| `tanggal_mulai` | datetime | — |
| `tanggal_selesai` | datetime nullable | — |
| `lampiran_path` | string nullable | PDF/gambar |
| `created_by` | bigint FK → users | — |
| `created_at` | timestamp | — |

### `announcement_reads`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `announcement_id` | bigint FK | — |
| `user_id` | bigint FK | — |
| `read_at` | datetime | — |

## 6D.3 Halaman

### Crew
- **"Pengumuman"** — list, badge "belum dibaca", tap → detail
- **Badge di dashboard** — jumlah pengumuman belum dibaca

### Supervisor/HRD/Owner
- **"Kelola Pengumuman"** — CRUD, pilih target role
- **"Statistik Baca"** — siapa sudah baca, siapa belum

## 6D.4 Aturan Bisnis
- **Target role** — crew hanya lihat yang target-nya crew
- **Prioritas `urgent`** — muncul sebagai popup di dashboard crew
- **Lampiran** — max 5 MB, PDF/gambar
- **Kedaluwarsa** — otomatis hilang setelah `tanggal_selesai`
- **Tidak perlu approval** — pengirim langsung publish

## 6D.5 Kriteria Selesai 6D
| # | Kriteria |
|---|---|
| 1 | HRD bisa buat pengumuman |
| 2 | Target role berfungsi |
| 3 | Crew lihat di dashboard |
| 4 | Badge "belum dibaca" |
| 5 | Lampiran bisa diunduh |
| 6 | Urgent muncul sebagai popup |
| 7 | Statistik baca tampil |
| 8 | Activity log tercatat |

---

# 🟨 SUB-BLOK 6E — Koreksi Self-Service

## 6E.1 Konsep
Crew bisa **ajukan koreksi absen sendiri** (mis. lupa absen) → HRD review → approve/reject → berita acara.

> **Catatan:** Blok 4 sudah punya koreksi yang diproses HRD. Blok 6E menambah **pengajuan dari crew**.

## 6E.2 Tabel Baru

### `attendance_correction_requests`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | Pengaju |
| `attendance_id` | bigint nullable FK | Kalau absen sudah ada |
| `tanggal` | date | Tanggal yang mau dikoreksi |
| `jenis` | enum | `lupa_masuk`, `lupa_pulang`, `jam_salah`, `tanggal_salah` |
| `jam_benar` | time nullable | — |
| `alasan` | text | — |
| `bukti_path` | string nullable | — |
| `status` | enum | `pending`, `approved`, `rejected` |
| `diproses_oleh` | bigint nullable | HRD |
| `catatan_hrd` | text nullable | — |
| `attendance_correction_id` | bigint nullable | Relasi ke Blok 4 |
| `created_at` | timestamp | — |

## 6E.3 Halaman

### Crew
- **"Ajukan Koreksi Absen"** — form: tanggal, jenis, jam benar, alasan, bukti
- **"Riwayat Koreksi"** — status pengajuan

### HRD
- **"Permintaan Koreksi"** — list pending, review, approve/reject
- Approve → otomatis buat `attendance_correction` (Blok 4) + berita acara

## 6E.4 Aturan Bisnis
- **Hanya untuk absen sendiri** — tidak bisa koreksi crew lain
- **Maksimal H+7** — ajukan koreksi (cegah koreksi lama)
- **Bukti wajib** untuk `lupa_masuk`/`lupa_pulang`
- **Approve** → status absen `corrected` + berita acara
- **Reject** → crew dapat notifikasi + alasan

## 6E.5 Kriteria Selesai 6E
| # | Kriteria |
|---|---|
| 1 | Crew bisa ajukan koreksi |
| 2 | Bukti bisa diupload |
| 3 | HRD lihat daftar permintaan |
| 4 | Approve → berita acara otomatis |
| 5 | Reject → notifikasi crew |
| 6 | Batas H+7 berlaku |
| 7 | Activity log tercatat |

---

# 🟩 SUB-BLOK 6F — Issue Report

## 6F.1 Konsep
Crew bisa **lapor masalah** (equipment rusak, customer complaint, dll) → supervisor/HRD tindak lanjut.

## 6F.2 Tabel Baru

### `issue_reports`
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `employee_id` | bigint FK | Pelapor |
| `kategori` | enum | `equipment`, `customer`, `safety`, `kebersihan`, `lainnya` |
| `judul` | string | — |
| `deskripsi` | text | — |
| `foto_path` | string nullable | — |
| `prioritas` | enum | `rendah`, `sedang`, `tinggi` |
| `status` | enum | `open`, `in_progress`, `resolved`, `closed` |
| `ditangani_oleh` | bigint nullable | — |
| `catatan_penanganan` | text nullable | — |
| `created_at` | timestamp | — |
| `resolved_at` | datetime nullable | — |

## 6F.3 Halaman

### Crew
- **"Lapor Masalah"** — form: kategori, judul, deskripsi, foto
- **"Laporan Saya"** — status laporan

### Supervisor
- **"Issue Report"** — list semua, filter status, ubah status, catatan

### HRD
- **"Issue Report"** — read-only + laporan

## 6F.4 Aturan Bisnis
- **Tidak perlu approval** — laporan langsung masuk
- **Foto opsional** tapi disarankan
- **Status diubah** oleh supervisor
- **Notifikasi** ke supervisor saat ada laporan baru
- **Tidak bisa dihapus** — audit

## 6F.5 Kriteria Selesai 6F
| # | Kriteria |
|---|---|
| 1 | Crew bisa lapor masalah |
| 2 | Foto bisa diupload |
| 3 | Supervisor lihat daftar |
| 4 | Supervisor ubah status |
| 5 | Crew dapat notifikasi update |
| 6 | Activity log tercatat |

---

## 3. Urutan Pengerjaan Blok 6

| Sub-Blok | Fitur | Alasan Urutan |
|---|---|---|
| **6A** | Cuti & Izin | Paling sering dipakai, butuh approval berjenjang |
| **6B** | Slip Gaji | Butuh data absensi `final` (sudah ada dari Blok 4–5) |
| **6C** | Jadwal Shift | Butuh employee + role (sudah ada) |
| **6D** | Broadcast | Paling murah & cepat |
| **6E** | Koreksi Self-Service | Perluas Blok 4 dengan pengajuan crew |
| **6F** | Issue Report | Nice to have |

---

## 4. Fitur Tambahan di Blok 6 (Opsional)

| Fitur | Prioritas | Catatan |
|---|---|---|
| **Push Notification PWA** | 🟡 Sedang | Butuh service worker + VAPID key |
| **2FA (Two-Factor Auth)** | 🟡 Sedang | Untuk Owner & HRD |
| **Export Excel** | 🟢 Rendah | Laporan absensi & gaji |
| **Chat Internal** | 🟢 Rendah | WhatsApp sudah cukup |
| **Face Recognition Absen** | 🟢 Rendah | Overkill untuk 10 crew |
| **Multi-Outlet** | 🟢 Rendah | Nanti kalau ada cabang |
| **Dashboard Keuangan** | 🟢 Rendah | Setelah gaji stabil |
| **Gamifikasi / Leaderboard** | 🟢 Rendah | Nice to have |

---

## 5. Ringkasan Blok 6

| Aspek | Isi |
|---|---|
| **Tujuan** | Lengkapi modul HRD selain absensi |
| **Sub-Blok** | 6A Cuti, 6B Gaji, 6C Jadwal, 6D Broadcast, 6E Koreksi Self, 6F Issue |
| **Tabel baru** | ±10 tabel (per sub-blok) |
| **PDF baru** | Surat Cuti, Slip Gaji, Jadwal Mingguan |
| **Halaman baru** | ±15 halaman (per role) |
| **Aturan** | Selesaikan satu sub-blok end-to-end sebelum lanjut |
| **Yang belum** | Push notif, 2FA, export Excel (opsional) |

---

## 6. Status Keseluruhan Proyek (Setelah Blok 6)

| Blok | Modul | Status |
|---|---|---|
| **1** | Fondasi | ✅ |
| **2** | Crew: Absensi | ✅ |
| **3** | Supervisor: Validasi | ✅ |
| **4** | HRD: Verifikasi | ✅ |
| **5** | Owner: Dashboard | ✅ |
| **6A** | Cuti & Izin | ✅ |
| **6B** | Slip Gaji | ✅ |
| **6C** | Jadwal Shift | ✅ |
| **6D** | Broadcast | ✅ |
| **6E** | Koreksi Self-Service | ✅ |
| **6F** | Issue Report | ✅ |

**Aplikasi Kafeku = 100% selesai** untuk kebutuhan coffee shop 10 crew.

---

## 7. Setelah Semua Selesai — Langkah Produksi

| # | Langkah | Catatan |
|---|---|---|
| 1 | **Deploy ke VPS** | Nginx + HTTPS + PHP 8.2 |
| 2 | **Backup otomatis** | Harian ke Google Drive |
| 3 | **Monitoring** | Log error, uptime |
| 4 | **Uji coba dengan 2–3 crew** | Sebelum full rollout |
| 5 | **Onboarding crew** | 1 sesi training 30 menit |
| 6 | **PWA install** | Crew install di HP |
| 7 | **Ganti password default** | Wajib |
| 8 | **Audit awal** | Cek activity log |
| 9 | **Dokumentasi** | SOP untuk HRD & Supervisor |
| 10 | **Feedback loop** | Kumpulkan masukan 1 bulan pertama |

---

## 📘 Ringkasan Blueprint Lengkap (Blok 1–6)

| Blok | Fokus | Hasil |
|---|---|---|
| **1** | Fondasi | Login, role, karyawan, setting |
| **2** | Absensi Crew | Crew absen GPS + selfie |
| **3** | Validasi Supervisor | `pending → validated` + PDF Harian |
| **4** | Verifikasi HRD | `validated → final` + PDF Final + Berita Acara |
| **5** | Owner Dashboard | Laporan + setting + rekap bulanan |
| **6** | Fitur Lain | Cuti, gaji, jadwal, broadcast, koreksi, issue |

**Total: 6 blok · ±15 tabel · ±30 halaman · ±5 jenis PDF · 4 role · 1 aplikasi: Kafeku — by AchmatFadilah Dev.**

---