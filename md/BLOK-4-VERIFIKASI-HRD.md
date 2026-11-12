# 📘 BLOK 4 — HRD: Verifikasi + PDF Rekap Final + Berita Acara (Detail Teknis, Tanpa Koding)

Tujuan Blok 4: **HRD memverifikasi absen yang sudah divalidasi supervisor, mencocokkan dengan bukti pendukung, mengoreksi jika perlu, lalu menghasilkan PDF Rekap Final + Berita Acara.** Ini blok **paling kritis** — di sini absen jadi `final` dan terkunci. Fokus: **verifikasi berbukti, koreksi berjejak, laporan otoritatif.**

---

## 1. Prasyarat (Harus Sudah dari Blok 1–3)

| Prasyarat | Status |
|---|---|
| Login, role, permission jalan | ✅ Blok 1 |
| Crew bisa absen (`pending`) | ✅ Blok 2 |
| Supervisor validasi (`validated` / `disputed`) | ✅ Blok 3 |
| PDF Rekap Harian terkirim ke HRD | ✅ Blok 3 |
| Tabel `attendance_evidences` siap | ✅ Blok 3 |
| Tabel `attendance_reports` siap | ✅ Blok 3 |
| SMTP email jalan | ✅ Blok 3 |

Kalau supervisor belum bisa validasi, **Blok 3 belum selesai** — jangan lanjut.

---

## 2. Konsep Verifikasi HRD (Pahami Dulu)

### Apa itu verifikasi?
HRD **mencocokkan** absen yang sudah divalidasi supervisor dengan **bukti pendukung**, lalu **memastikan** absen itu sah.

### Perbedaan Validasi vs Verifikasi
| Aspek | Supervisor (Blok 3) | HRD (Blok 4) |
|---|---|---|
| **Fokus** | Cepat: foto, GPS, jam | Teliti: cocokkan bukti |
| **Waktu** | Real-time / akhir shift | Sore/malam / besok |
| **Bukti** | Lihat foto & GPS | Cocokkan CCTV, log POS, dll |
| **Kewenangan** | Validasi / disputed | Verifikasi / selisih / koreksi |
| **Hasil** | `validated` / `disputed` | `verified` / `corrected` / `disputed` |
| **Bisa koreksi?** | ❌ Tidak | ✅ Ya (dengan berita acara) |

### Prinsip verifikasi
- **Berbukti** — jangan verifikasi tanpa bukti pendukung (kecuali kasus jelas)
- **Teliti tapi efisien** — jangan tiap absen butuh 10 menit
- **Koreksi transparan** — semua koreksi wajib berita acara
- **Final itu final** — setelah `final`, terkunci
- **Semua tercatat** — activity log wajib

---

## 3. Perubahan Status (State Transition di Blok 4)

### Alur status lengkap
```
pending ──► validated ──► verified ──► final
                │             ▲
                │             │
                └──► disputed ┘ (HRD bisa verifikasi setelah klarifikasi)
                        │
                        ▼
                   corrected ──► final
```

### Transisi yang ditangani Blok 4
| Dari | Ke | Oleh | Syarat |
|---|---|---|---|
| `validated` | `verified` | HRD | Cocok dengan bukti |
| `validated` | `disputed` | HRD | Ada selisih, perlu klarifikasi |
| `validated` | `corrected` | HRD | Ada koreksi jam/tanggal |
| `disputed` (dari supervisor) | `verified` | HRD | Setelah klarifikasi crew |
| `disputed` (dari supervisor) | `corrected` | HRD | Setelah koreksi |
| `verified` | `final` | Sistem | Otomatis setelah semua diverifikasi |
| `corrected` | `final` | Sistem | Otomatis setelah berita acara dibuat |

### Aturan transisi
- **`verified` dan `corrected`** langsung jadi `final` setelah semua absen hari itu selesai diverifikasi
- **`final` terkunci** — perubahan hanya via koreksi resmi baru
- **`disputed` bisa berubah** setelah crew klarifikasi (Blok 6) atau HRD putuskan

### Yang disimpan saat verifikasi
| Kolom (di `attendances`) | Isi |
|---|---|
| `status` | `verified` / `corrected` / `disputed` |
| `verified_by` | `user_id` HRD |
| `verified_at` | Timestamp verifikasi |
| `catatan_hrd` | Catatan HRD |
| `jam_masuk` (kalau corrected) | Jam yang benar |
| `jam_pulang` (kalau corrected) | Jam yang benar |

**Catatan:** kolom ini **ditambahkan** ke tabel `attendances` di Blok 4.

## 4. Tabel Baru: `attendance_corrections`

### Fungsi
Menyimpan **setiap koreksi absen** — dengan alasan, bukti, dan berita acara. Ini **jejak audit** koreksi.

### Kolom inti
| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | PK |
| `attendance_id` | bigint FK → attendances | Absen yang dikoreksi |
| `diminta_oleh` | bigint FK → users nullable | Crew yang minta (kalau self-service, Blok 6) |
| `diproses_oleh` | bigint FK → users | HRD yang proses |
| `jenis` | enum | `jam_masuk`, `jam_pulang`, `tanggal`, `status` |
| `nilai_lama` | string | Nilai sebelum |
| `nilai_baru` | string | Nilai sesudah |
| `alasan` | text | Alasan koreksi |
| `bukti_path` | string nullable | Path bukti (kalau ada) |
| `berita_acara_no` | string nullable | Nomor berita acara |
| `berita_acara_path` | string nullable | Path PDF berita acara |
| `status` | enum | `draft`, `approved`, `rejected` |
| `created_at` | timestamp | — |

### Aturan
- **1 koreksi = 1 record** — kalau koreksi jam masuk & pulang, bisa 1 record dengan 2 field, atau 2 record terpisah (pilih: **1 record per field** untuk audit granular)
- **Wajib ada berita acara** kalau status `approved`
- **Tidak bisa dihapus** — audit permanen
- **Relasi ke `attendance_reports`** — berita acara punya nomor dokumen sendiri

## 6. Halaman: Verifikasi Absensi (HRD)

### Fungsi
Halaman utama HRD untuk **melihat absen `validated`** dan memverifikasi.

### Alur tampilan
```
HRD buka app
  ↓
Dashboard HRD: kartu "Verifikasi Absensi — 8 validated"
  ↓
Tap → halaman "Verifikasi Absensi"
  ↓
Daftar absen, dikelompokkan:
  ├── Validated (perlu verifikasi)
  ├── Disputed (perlu klarifikasi)
  ├── Verified (sudah OK)
  └── Corrected (sudah dikoreksi)
  ↓
Tap satu crew → detail verifikasi
  ↓
Verifikasi / selisih / koreksi
  ↓
Kembali ke daftar → status berubah
```

### Komponen halaman
| Komponen | Fungsi |
|---|---|
| **Filter tanggal** | Default hari ini, bisa ganti |
| **Filter status** | Semua / Validated / Disputed / Verified / Corrected |
| **Filter posisi** | Kitchen / Bar / Kasir (opsional) |
| **Ringkasan atas** | "8 validated · 1 disputed · 2 verified" |
| **Daftar absen** | Per baris: nama, posisi, jam masuk, status badge, jumlah bukti |
| **Tombol "Verifikasi Semua"** | Verifikasi massal (dengan konfirmasi + syarat) |
| **Tap baris** | Buka detail verifikasi |

### Aturan UX
- **Validated di atas** — HRD langsung lihat yang perlu dikerjakan
- **Badge jumlah bukti** — kalau sudah ada bukti, tampilkan "2 bukti"
- **Mobile-friendly** — HRD mungkin buka dari HP
- **Bisa buka dari email** — link di email PDF mengarah ke halaman ini

### Hasil
- HRD lihat semua yang perlu diverifikasi dalam 1 halaman
- Bisa mulai dari mana saja

## 7. Halaman: Detail Verifikasi (HRD)

### Fungsi
Halaman untuk **mencocokkan absen dengan bukti** dan memutuskan.

### Alur tampilan
```
Tap crew dari daftar
  ↓
Halaman detail verifikasi:
  ├── Tab 1: Data Absen
  │    ├── Foto masuk (besar)
  │    ├── Peta GPS + jarak
  │    ├── Jam masuk · akurasi · device · IP
  │    └── Foto pulang (kalau sudah)
  ├── Tab 2: Bukti Pendukung
  │    ├── Daftar bukti yang sudah diupload
  │    ├── Form upload bukti baru
  │    └── Preview bukti
  ├── Tab 3: Riwayat
  │    └── Log validasi supervisor + aktivitas
  └── Tombol aksi (sticky di bawah):
       ├── [✅ Verifikasi OK]
       ├── [⚠️ Tandai Selisih]
       └── [📝 Koreksi]
```

### Tab 1: Data Absen
| Komponen | Fungsi |
|---|---|
| Foto masuk | Full size, zoom |
| Peta | Pin crew + pin coffee shop + radius |
| Info masuk | Jam, jarak, akurasi, device, IP |
| Foto pulang | Sama |
| Info pulang | Sama |
| Status validasi supervisor | Siapa, kapan, catatan |

### Tab 2: Bukti Pendukung
| Komponen | Fungsi |
|---|---|
| Daftar bukti | Per bukti: jenis, file/keterangan, siapa upload, kapan |
| Tombol upload | Upload bukti baru (file atau keterangan) |
| Jenis bukti | Dropdown: CCTV, log POS, foto saksi, chat, konfirmasi supervisor, lainnya |
| Preview | Klik bukti → lihat file/keterangan |

### Tab 3: Riwayat
| Komponen | Fungsi |
|---|---|
| Timeline | Absen → validasi supervisor → verifikasi HRD (kalau ada) |
| Activity log | Semua aksi terkait absen ini |

### Tombol Aksi
- **Verifikasi OK** → status `verified` → nanti `final`
- **Tandai Selisih** → status `disputed` + form alasan
- **Koreksi** → status `corrected` + form koreksi + generate berita acara

### Aturan UX
- **Tab jelas** — HRD tidak bingung
- **Bukti di tengah** — fokus pada pencocokan
- **Tombol aksi sticky** — mudah dijangkau
- **Konfirmasi sebelum aksi** — "Yakin verifikasi absen Budi?"
- **Form koreksi jelas** — tunjukkan nilai lama vs baru

### Hasil
- HRD bisa verifikasi dengan **informasi lengkap**
- Tidak perlu buka aplikasi lain (CCTV bisa diupload screenshot)

## 8. Aksi: Verifikasi OK

### Fungsi
HRD **memastikan** absen sah — cocok dengan bukti.

### Cara kerja
1. HRD tap **"Verifikasi OK"**
2. Muncul konfirmasi: "Verifikasi absen [nama] tanggal [tgl]?"
3. HRD konfirmasi
4. Sistem:
   - Update `status` → `verified`
   - Isi `verified_by`, `verified_at`
   - Catat di `activity_logs`
5. Cek: semua absen hari itu sudah `verified`/`corrected`?
   - Kalau ya → trigger generate PDF Rekap Final (lihat bagian 12)
6. Kembali ke daftar

### Aturan
- **Hanya HRD** yang bisa verifikasi (permission `attendance.verify`)
- **Hanya status `validated`** yang bisa diverifikasi
- **HRD tidak bisa verifikasi absen sendiri** — Owner yang verifikasi (aturan bisnis)
- **Bukti pendukung opsional** untuk verifikasi normal — tapi **sangat disarankan** untuk absen yang mencurigakan

### Kapan bukti wajib?
| Kondisi | Bukti Wajib? |

## 10. Aksi: Koreksi

### Fungsi
HRD **mengoreksi** absen yang salah — jam, tanggal, atau status.

### Kapan dipakai
- Crew lupa absen (setelah klarifikasi)
- Jam absen salah (HP error)
- Tanggal salah (absen lewat tengah malam)
- Status perlu diubah (mis. dari hadir jadi izin)

### Cara kerja
1. HRD tap **"Koreksi"**
2. Muncul form:
   - **Jenis koreksi** (wajib) — jam masuk / jam pulang / tanggal / status
   - **Nilai lama** (readonly, ditampilkan)
   - **Nilai baru** (wajib) — input
   - **Alasan** (wajib) — kenapa dikoreksi
   - **Bukti** (wajib) — upload bukti
3. HRD simpan
4. Sistem:
   - Simpan record di `attendance_corrections`
   - Update `attendances` dengan nilai baru
   - Set `is_corrected=true`
   - Update `status` → `corrected`
   - Isi `verified_by`, `verified_at`, `catatan_hrd`
   - Generate **PDF Berita Acara Koreksi** (lihat bagian 13)
   - Catat di `activity_logs`
5. Notifikasi ke crew: "Absenmu dikoreksi — [detail]"
6. Notifikasi ke supervisor: "Absen [nama] dikoreksi HRD"
7. Kembali ke daftar

### Form koreksi — detail
| Field | Wajib | Keterangan |
|---|---|---|
| Jenis koreksi | ✅ | Dropdown |
| Nilai lama | ✅ (readonly) | Ditampilkan otomatis |
| Nilai baru | ✅ | Input |
| Alasan | ✅ | Text |
| Bukti | ✅ | Upload file/keterangan |
| Minta klarifikasi crew dulu? | ❌ | Checkbox (kalau ya, status jadi disputed dulu) |

### Aturan
- **Alasan & bukti wajib** — tidak ada koreksi tanpa jejak
- **Berita acara otomatis dibuat**
- **Crew tidak bisa koreksi sendiri** — hanya HRD
- **HRD tidak bisa koreksi absen sendiri** — Owner yang koreksi
- **Koreksi bisa dibatalkan?** — Tidak. Kalau salah, koreksi baru (dengan berita acara baru)

### Hasil
- Status `validated → corrected`
- Berita acara PDF dibuat
- Crew & supervisor dapat notifikasi

## 11. Aksi: Verifikasi Massal

### Fungsi
HRD **memverifikasi semua** absen `validated` yang **tidak bermasalah** sekaligus.

### Cara kerja
1. HRD tap **"Verifikasi Semua"**
2. Muncul konfirmasi: "Verifikasi 8 absen validated? Absen tanpa bukti akan diverifikasi tanpa bukti."
3. HRD konfirmasi
4. Sistem:
   - Loop semua absen `validated` hari itu
   - Update ke `verified` dengan `verified_by` HRD
   - Catat 1 activity log batch
5. Cek: semua sudah verified/corrected? → trigger PDF Rekap Final

### Aturan
- **Hanya `validated`** — yang `disputed` tidak disentuh
- **Konfirmasi wajib**
- **Peringatan**: absen tanpa bukti akan diverifikasi tanpa bukti — HRD harus sadar
- **Kalau ada yang aneh**, jangan pakai massal — verifikasi satu per satu, tandai yang bermasalah

### Hasil
- HRD hemat waktu kalau semua OK
- Tetap bisa teliti kalau ada yang aneh
|---|---|
| Absen normal (dalam radius, foto jelas) | ❌ Opsional |
| Absen di tepi radius (>80% radius) | ✅ Wajib |
| Absen terlambat | ⚠️ Disarankan |
| Absen dengan catatan supervisor | ✅ Wajib |
| Absen `disputed` dari supervisor | ✅ Wajib |

### Hasil
- Status berubah `validated → verified`
- Menunggu semua selesai → `final`

## 9. Aksi: Tandai Selisih (Disputed)

### Fungsi
HRD **menandai** absen yang **tidak cocok** dengan bukti — perlu klarifikasi.

### Kapan dipakai
- CCTV menunjukkan jam beda
- Log POS tidak cocok
- Foto selfie tidak cocok dengan CCTV
- Crew absen tapi tidak terlihat di CCTV
- Bukti pendukung tidak mendukung

### Cara kerja
1. HRD tap **"Tandai Selisih"**
2. Muncul form:
   - **Alasan** (wajib) — dropdown + free text
   - **Bukti** (wajib) — upload CCTV/log POS/dll
   - **Klarifikasi yang diminta** (wajib) — apa yang perlu crew jelaskan
3. HRD simpan
4. Sistem:
   - Update `status` → `disputed`
   - Isi `catatan_hrd`
   - Simpan bukti ke `attendance_evidences`
   - Catat di `activity_logs`
5. Notifikasi ke crew: "Absenmu perlu klarifikasi — [alasan]"
6. Kembali ke daftar

### Alasan yang sering dipakai
- CCTV tidak cocok
- Log POS tidak cocok
- Foto tidak cocok
- Lokasi tidak sesuai
- Jam tidak wajar
- Lainnya

### Aturan
- **Alasan wajib**
- **Bukti wajib** — jangan tandai selisih tanpa bukti
- **Klarifikasi jelas** — crew tahu harus jelaskan apa
- **Crew bisa klarifikasi** di Blok 6 (self-service)

## 12. Trigger Generate PDF Rekap Final

### Fungsi
PDF Rekap Final **otomatis dibuat** setelah semua absen hari itu `verified`/`corrected`.

### Kapan trigger?
| Kondisi | Aksi |
|---|---|
| **Semua absen hari itu sudah `verified`/`corrected`** | Auto-generate PDF Rekap Final |
| **HRD klik "Selesaikan Verifikasi"** (tombol manual) | Auto-generate PDF Rekap Final |
| **Jam 23:59** (cron) | Generate PDF untuk yang sudah verified |

### Kenapa 3 trigger?
- **Otomatis** — kalau HRD rajin, PDF langsung jadi
- **Manual** — HRD kontrol kapan selesai
- **Cron** — jaring pengaman kalau HRD lupa

### Aturan
- **1 PDF Rekap Final per hari** — tidak dobel
- **PDF Rekap Final berbeda dari Rekap Harian** (Blok 3) — Rekap Final punya info bukti & koreksi
- **Kalau ada koreksi baru setelah PDF dibuat**, **regenerate** PDF (dengan nomor dokumen baru)
- PDF disimpan permanen

### Hasil
- PDF Rekap Final otomatis terkirim ke Owner
- Absen hari itu jadi `final` & terkunci

## 13. PDF Rekap Final — Spesifikasi

### Nama file
```
KFK-ABF-{YYYY-MM-DD}-{urut}.pdf
Contoh: KFK-ABF-2026-11-11-001.pdf
```

### Isi PDF
| Bagian | Isi |
|---|---|
| **Header** | Logo Kafeku + "REKAP ABSENSI FINAL" |
| **Info** | Tanggal · Supervisor · HRD · Jumlah crew |
| **Tabel utama** | No · Nama · Posisi · Masuk · Pulang · Jarak · Status · Bukti · Catatan |
| **Daftar koreksi** | Kalau ada: nama, field, lama → baru, alasan |
| **Ringkasan** | Hadir · Terlambat · Disputed · Koreksi · Absen |
| **Tanda tangan** | Supervisor (validasi) · HRD (verifikasi) · Owner (approval, opsional) |
| **Footer** | Nomor dokumen · Waktu dibuat · Halaman · `© 2026 Kafeku · by AchmatFadilah Dev` |

### Perbedaan dengan Rekap Harian (Blok 3)
| Aspek | Rekap Harian | Rekap Final |
|---|---|---|
| Status | `validated` | `verified` / `corrected` |
| Kolom bukti | ❌ | ✅ |
| Daftar koreksi | ❌ | ✅ |
| Tanda tangan HRD | ❌ | ✅ |
| Terkirim ke | HRD | Owner |

### Aturan tampilan
- **Status pakai warna** — Final (hijau), Koreksi (kuning), Disputed (merah)
- **Font jelas**
- **A4 portrait**
- **Nomor dokumen unik**

### Hasil
- PDF Rekap Final rapi, lengkap, otoritatif
- Siap diarsipkan & jadi bukti resmi
- **Sementara**, crew bisa klarifikasi manual ke HRD

### Hasil
- Status `validated → disputed`
- Crew dapat notifikasi
- Bukti tersimpan

### Hasil
- Semua koreksi tercatat dengan bukti
- Bisa diaudit kapan saja

## 5. Migration Tambahan ke `attendances`

### Kolom yang ditambahkan di Blok 4
| Kolom | Tipe | Keterangan |
|---|---|---|
| `verified_by` | bigint nullable FK → users | HRD yang verifikasi |
| `verified_at` | datetime nullable | Kapan diverifikasi |
| `catatan_hrd` | text nullable | Catatan HRD |
| `is_corrected` | boolean default false | Apakah pernah dikoreksi |
| `final_at` | datetime nullable | Kapan jadi `final` |
| `locked` | boolean default false | Terkunci untuk edit |

### Kenapa `locked`
- Setelah `final`, data **tidak bisa diubah** oleh siapa pun (kecuali koreksi resmi baru)
- Middleware/observer cek `locked` sebelum update
- Audit lebih bersih

### Hasil
- Tabel siap menampung verifikasi & koreksi
- Data final terkunci

## 14. PDF Berita Acara Koreksi — Spesifikasi

### Nama file
```
KFK-BAK-{YYYY-MM-DD}-{urut}.pdf
Contoh: KFK-BAK-2026-11-11-001.pdf
```

### Isi PDF
| Bagian | Isi |
|---|---|
| **Header** | Logo Kafeku + "BERITA ACARA KOREKSI ABSENSI" |
| **Nomor dokumen** | `KFK-BAK-2026-11-11-001` |
| **Data karyawan** | Nama, NIK, posisi |
| **Absen asli** | Tanggal, jam masuk, jam pulang, status |
| **Koreksi** | Field, nilai lama → nilai baru |
| **Alasan** | Kenapa dikoreksi |
| **Bukti** | Daftar bukti (nama file/keterangan) |
| **Ditandatangani** | HRD (yang koreksi) · Supervisor (yang validasi) · Crew (yang menerima, opsional) |
| **Footer** | Waktu dibuat · Halaman · `© 2026 Kafeku · by AchmatFadilah Dev` |

### Aturan
- **Dibuat otomatis** setiap ada koreksi
- **Wajib ada** untuk setiap koreksi
- **Disimpan permanen** — tidak bisa dihapus
- **Dikirim ke**: Crew terkait, Supervisor, Owner

### Hasil
- Setiap koreksi punya **bukti resmi**
- Tidak bisa dibantah
- Transparan

## 15. Distribusi PDF di Blok 4

### Rekap Final
| Penerima | Kanal | Kenapa |
|---|---|---|
| **Owner** | In-app + email (attach PDF) | Transparansi, approval |
| **HRD** | In-app (arsip) | Arsip sendiri |
| **Supervisor** | In-app (opsional) | Feedback |

### Berita Acara Koreksi
| Penerima | Kanal | Kenapa |
|---|---|---|
| **Crew terkait** | In-app + email (attach PDF) | Bukti koreksi |
| **Supervisor** | In-app + email | Info |
| **Owner** | In-app + email | Transparansi |

### Aturan
- **Owner dapat Rekap Final** — wajib
- **Crew dapat Berita Acara** — wajib (kalau ada koreksi)
- **PDF disimpan di aplikasi** — tidak bergantung email
- **Email retry** — 3× dengan jeda

### Hasil
- Semua pihak terkait dapat PDF
- Arsip lengkap

## 16. Halaman: Arsip Dokumen (HRD)

### Fungsi
HRD bisa lihat **semua PDF** yang sudah dibuat — Rekap Harian, Rekap Final, Berita Acara.

### Tampilan
| Komponen | Isi |
|---|---|
| **Filter jenis** | Rekap Harian / Rekap Final / Berita Acara / Bulanan |
| **Filter bulan** | Default bulan ini |
| **Filter karyawan** | (untuk Berita Acara) |
| **Daftar PDF** | Per PDF: nomor dokumen, jenis, tanggal, status kirim |
| **Tombol unduh** | Unduh PDF |
| **Tombol kirim ulang** | Kirim ulang email (kalau perlu) |

### Aturan
- **Read-only** — HRD tidak bisa edit PDF
- **Bisa unduh kapan saja**
- **Kirim ulang** — kalau penerima bilang tidak terima
- **Hanya HRD & Owner** yang bisa akses arsip lengkap
- **Supervisor & Crew** hanya lihat PDF terkait dirinya

### Hasil
- HRD punya arsip lengkap
- Bisa lacak & kirim ulang

## 17. Notifikasi di Blok 4

### Notifikasi yang muncul
| Event | Penerima | Kanal |
|---|---|---|
| HRD verifikasi absen | Crew (opsional) | In-app |
| HRD tandai selisih | Crew | In-app + email |
| HRD koreksi absen | Crew + Supervisor | In-app + email (attach Berita Acara) |
| PDF Rekap Final dibuat | Owner | In-app + email |
| Email gagal kirim | HRD + Owner | In-app |
| Crew klarifikasi (Blok 6) | HRD | In-app (siapkan, implementasi Blok 6) |

### Aturan
- **Crew wajib dapat notifikasi koreksi** — dengan Berita Acara
- **Owner wajib dapat Rekap Final**
- **Email gagal** — jangan diam

### Hasil
- Semua pihak tahu status
- Transparan

## 18. Keamanan Verifikasi (Wajib)

| # | Aturan | Kenapa |
|---|---|---|
| 1 | **Hanya HRD** bisa verifikasi | Permission `attendance.verify` |
| 2 | **HRD tidak bisa verifikasi absen sendiri** | Cegah konflik kepentingan — Owner yang verifikasi |
| 3 | **HRD tidak bisa koreksi absen sendiri** | Owner yang koreksi |
| 4 | **Koreksi wajib bukti** | Cegah koreksi asal-asalan |
| 5 | **Berita acara wajib** untuk koreksi | Jejak audit |
| 6 | **Data `final` terkunci** | `locked=true` — tidak bisa edit |
| 7 | **Bukti disimpan privat** | Akses via route bertoken |
| 8 | **PDF disimpan privat** | Akses via route bertoken |
| 9 | **Activity log wajib** | Setiap verifikasi/koreksi tercatat |
| 10 | **Rate limit** | Max 100 verifikasi per menit |
| 11 | **Konfirmasi sebelum aksi** | Cegah salah klik |
| 12 | **Supervisor tidak bisa ubah `verified`** | Terkunci |

### Kenapa HRD tidak bisa verifikasi sendiri
- **Konflik kepentingan** — HRD bisa "membenarkan" absennya sendiri
- **Owner yang verifikasi HRD** — aturan bisnis
- Kalau Owner tidak ada, HRD bisa verifikasi dengan **catatan "self-verified, pending owner"** — tapi tetap perlu approval Owner nanti

### Hasil
- Verifikasi **adil & tercatat**
- Tidak bisa dimanipulasi

## 19. Kriteria Selesai Blok 4 (Definition of Done)

Blok 4 **selesai** kalau semua ini lolos:

| # | Kriteria | Cara Uji |
|---|---|---|
| 1 | HRD lihat daftar absen validated | Login HRD → buka "Verifikasi Absensi" |
| 2 | Daftar dikelompokkan per status | Validated di atas, verified di bawah |
| 3 | HRD bisa lihat detail verifikasi | Tap crew → tab data absen + bukti + riwayat |
| 4 | HRD bisa upload bukti pendukung | Upload file → tersimpan → muncul di tab bukti |
| 5 | HRD bisa verifikasi OK | Tap verifikasi → status `verified` |
| 6 | HRD bisa verifikasi massal | Tap "Verifikasi Semua" → semua validated jadi verified |
| 7 | HRD bisa tandai selisih | Tap selisih → isi alasan + bukti → status `disputed` |
| 8 | Alasan & bukti wajib kalau selisih | Coba simpan tanpa → ditolak |
| 9 | HRD bisa koreksi | Tap koreksi → isi form → status `corrected` |
| 10 | Koreksi wajib alasan & bukti | Coba simpan tanpa → ditolak |
| 11 | Berita Acara PDF otomatis dibuat | Setelah koreksi → PDF muncul |
| 12 | HRD tidak bisa verifikasi sendiri | Coba verifikasi absen sendiri → ditolak |
| 13 | HRD tidak bisa koreksi sendiri | Coba koreksi absen sendiri → ditolak |
| 14 | PDF Rekap Final otomatis dibuat | Setelah semua verified → PDF muncul |
| 15 | PDF Rekap Final terkirim ke Owner | Cek inbox Owner → email dengan attach PDF |
| 16 | Berita Acara terkirim ke Crew | Cek inbox crew yang dikoreksi → email dengan attach PDF |
| 17 | Crew dapat notifikasi koreksi | Login crew → notifikasi muncul |
| 18 | Data `final` terkunci | Coba edit absen final → ditolak |
| 19 | Arsip dokumen bisa diakses | Buka arsip → semua PDF muncul |
| 20 | Activity log tercatat | Cek log → ada record verifikasi/koreksi |
| 21 | Tampilan jalan di HP | Uji HRD dari Android/iPhone |

**Kalau 21 poin ini lolos → Blok 4 selesai, lanjut Blok 5 (Owner: Dashboard).**

## 20. Yang TIDAK Dibuat di Blok 4

| ❌ Jangan | Alasan |
|---|---|
| Dashboard statistik Owner | Blok 5 |
| Pengaturan Owner | Blok 5 |
| Rekap Bulanan | Blok 5 |
| Ajukan koreksi oleh crew (self-service) | Blok 6 |
| Cuti & Izin | Blok 6 |
| Slip Gaji | Blok 6 |
| Jadwal Shift | Blok 6 |
| Push notification PWA | Blok 6 |
| WhatsApp notifikasi | Opsional nanti |
| Approval berjenjang Owner untuk koreksi | Cukup HRD di Blok 4; Owner bisa lihat di Blok 5 |
| Multi-outlet | Nanti kalau ada cabang |
| Koreksi massal | Satu per satu dulu — koreksi massal berisiko |

## 21. Urutan Pengerjaan di Blok 4

| Langkah | Yang Dikerjakan | Hasil |
|---|---|---|
| 1 | Migration tambah kolom verifikasi ke `attendances` | Kolom siap |
| 2 | Migration `attendance_corrections` | Tabel siap |
| 3 | Model `AttendanceCorrection` + relasi | Model siap |
| 4 | Halaman "Verifikasi Absensi" (daftar + filter) | HRD lihat daftar |
| 5 | Halaman detail verifikasi (3 tab) | HRD lihat detail |
| 6 | Form upload bukti pendukung | Bukti tersimpan |
| 7 | Aksi verifikasi OK | Status berubah |
| 8 | Aksi verifikasi massal | Hemat waktu |
| 9 | Aksi tandai selisih + form | Tandai bermasalah |
| 10 | Aksi koreksi + form | Koreksi jalan |
| 11 | Service generate Berita Acara PDF | PDF jadi |
| 12 | Service generate Rekap Final PDF | PDF jadi |
| 13 | Trigger otomatis + manual + cron | PDF auto |
| 14 | Distribusi email (Rekap Final, Berita Acara) | Email terkirim |
| 15 | Halaman arsip dokumen | Arsip siap |
| 16 | Notifikasi ke crew & owner | Komunikasi jalan |
| 17 | Middleware + permission cek | Keamanan |
| 18 | Lock data final | Data terkunci |
| 19 | Activity log otomatis | Audit |
| 20 | Uji 21 kriteria selesai | Blok 4 done |

## 22. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| **HRD lupa verifikasi** | Cron 23:59 generate PDF untuk yang verified; besok HRD verifikasi susulan |
| **HRD verifikasi asal-asalan** | Activity log + Owner bisa lihat + koreksi baru |
| **HRD koreksi tanpa bukti** | Form wajib bukti — tidak bisa simpan |
| **Berita Acara dobel** | Cek `attendance_reports` sebelum generate |
| **Email Berita Acara masuk spam** | SMTP kredibel + SPF/DKIM |
| **Crew tidak tahu dikoreksi** | Notifikasi in-app + email wajib |
| **Data final tetap bisa diubah** | Middleware cek `locked` — reject update |
| **Koreksi berantai** | Setiap koreksi baru punya berita acara baru — jejak jelas |
| **Storage bukti penuh** | Kompres file, batasi ukuran (max 5 MB per file) |
| **PDF Rekap Final tidak akurat** | Generate ulang setelah semua koreksi selesai |
| **Owner tidak approve** | Owner bisa lihat di Blok 5; koreksi tetap jalan (tidak butuh approval Owner untuk operasional) |

## 23. Ringkasan Blok 4

| Aspek | Isi |
|---|---|
| **Tujuan** | HRD verifikasi absen + koreksi + PDF Rekap Final + Berita Acara |
| **Tabel baru** | `attendance_corrections` + kolom verifikasi di `attendances` |
| **Halaman** | Verifikasi Absensi, Detail Verifikasi (3 tab), Arsip Dokumen |
| **Aksi** | Verifikasi (1 per 1 + massal), Tandai Selisih, Koreksi |
| **PDF** | Rekap Final (ke Owner), Berita Acara Koreksi (ke Crew, Supervisor, Owner) |
| **Email** | Rekap Final + Berita Acara |
| **Notifikasi** | Crew (disputed/koreksi), Owner (Rekap Final) |
| **Keamanan** | HRD tidak verifikasi/koreksi sendiri, data final terkunci, bukti wajib, activity log |
| **Kriteria selesai** | 21 poin |
| **Yang belum** | Dashboard Owner, rekap bulanan, fitur lain (cuti, gaji, jadwal) |

---

## 🎯 Setelah Blok 4 Selesai

Anda sudah punya **siklus absensi lengkap end-to-end**:

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
```

**Yang belum:**
- ❌ Owner dashboard & pengaturan (Blok 5)
- ❌ Rekap bulanan (Blok 5)
- ❌ Fitur lain: cuti, gaji, jadwal, broadcast, koreksi self-service (Blok 6)

**Blok 4 = absensi selesai end-to-end. Blok 5 = Owner melihat hasil. Blok 6 = fitur HRD lainnya.**

---

Mau saya lanjutkan ke **Blok 5 — Owner: Dashboard + Pengaturan + Rekap Bulanan** dengan detail teknis yang sama?