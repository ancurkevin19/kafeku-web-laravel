# 🎨 FRONTEND KAFEKU — Blueprint Teknis (Tanpa Koding)

Fokus frontend: **PWA mobile-first**, dipakai di HP Android/iPhone oleh crew, dan laptop oleh HRD/Owner. Stack ringan, konsisten dengan backend Laravel.

## 1. Stack Frontend

| Komponen | Pilihan | Alasan |
|---|---|---|
| Templating | Blade (Laravel bawaan) | Cepat, tidak perlu build terpisah |
| Interaktif | Livewire 3 | Form, tabel, filter tanpa JS kompleks |
| Styling | Tailwind CSS | Cepat, konsisten, mobile-first |
| Komponen UI | Flowbite atau DaisyUI | Modal, dropdown, toast siap pakai |
| Alpine.js | Bawaan Livewire | Interaksi kecil (toggle, dropdown) |
| Grafik | Chart.js atau ApexCharts | Dashboard Owner |
| Peta | Leaflet + OpenStreetMap | Gratis, ringan, cukup untuk radius |
| Kamera | MediaDevices API | Ambil foto langsung di browser |
| GPS | Geolocation API | Ambil koordinat |
| PWA | Manifest + Service Worker | Installable di HP |
| Icon | Heroicons atau Lucide | Konsisten, ringan |
| Font | Inter atau Plus Jakarta Sans | Modern, mudah dibaca |

**Tidak perlu:** React/Vue/Next.js, build tool kompleks, state management (Redux, Pinia).

## 2. Prinsip Desain

| Prinsip | Arti |
|---|---|
| Mobile-first | 90% crew pakai HP — desain HP dulu |
| Satu layout, sidebar dinamis | Bukan layout terpisah per role |
| Komponen reusable | Tombol, kartu, tabel, badge — sekali buat, pakai banyak |
| Konsisten | Warna, font, spacing sama di semua halaman |
| Cepat | Loading < 2 detik, gambar dikompres |
| Aksesibel | Kontras cukup, tombol besar, font jelas |
| Offline-friendly | Halaman dasar tetap bisa dibuka tanpa internet |
| Feedback jelas | Loading, sukses, error — selalu ada indikator |

## 3. Design System

### Warna
| Peran | Warna | Kode | Pakai Untuk |
|---|---|---|---|
| Primary | Coklat kopi | #6F4E37 | Header, tombol utama, sidebar aktif |
| Primary Light | Coklat muda | #A67B5B | Hover, aksen |
| Secondary | Krem | #F5EFE6 | Background halaman |
| Surface | Putih | #FFFFFF | Kartu, modal |
| Text Primary | Coklat tua | #2D1B0E | Teks utama |
| Text Secondary | Abu | #6B7280 | Teks pendukung |
| Success | Hijau | #3A7D44 | Status valid, approved |
| Warning | Kuning | #D97706 | Status pending, terlambat |
| Danger | Merah | #DC2626 | Status disputed, reject |
| Info | Biru | #2563EB | Notifikasi info |

### Tipografi
| Elemen | Font | Ukuran | Weight |
|---|---|---|---|
| Heading 1 | Plus Jakarta Sans | 24px | 700 |
| Heading 2 | Plus Jakarta Sans | 20px | 600 |
| Heading 3 | Plus Jakarta Sans | 16px | 600 |
| Body | Inter | 14px | 400 |
| Body kecil | Inter | 12px | 400 |
| Tombol | Inter | 14–16px | 600 |
| Angka dashboard | Plus Jakarta Sans | 32px | 700 |

### Spacing
- Skala: 4px, 8px, 12px, 16px, 24px, 32px, 48px
- Padding kartu: 16px (HP), 24px (laptop)
- Jarak antar elemen: 16px standar

### Radius & Shadow
- Radius tombol: 8px | Radius kartu: 12px | Radius modal: 16px | Shadow kartu: shadow-sm

### Tombol
| Jenis | Warna | Pakai Untuk |
|---|---|---|
| Primary | Coklat kopi | Aksi utama (Absen, Simpan, Validasi) |
| Secondary | Krem + border | Aksi sekunder (Batal, Kembali) |
| Success | Hijau | Verifikasi, Approve |
| Danger | Merah | Reject, Hapus, Disputed |
| Ghost | Transparan | Aksi ringan (Detail, Lihat) |

### Badge Status
| Status | Warna | Teks |
|---|---|---|
| pending | Abu | Menunggu |
| validated | Biru | Tervalidasi |
| verified | Hijau | Terverifikasi |
| disputed | Merah | Bermasalah |
| corrected | Kuning | Dikoreksi |
| final | Hijau tua | Final |
| approved | Hijau | Disetujui |
| rejected | Merah | Ditolak |

## 4. Struktur Folder

```
resources/views/
├── layouts/
│   ├── app.blade.php          → layout utama (sidebar + header)
│   ├── guest.blade.php        → layout login
│   └── pdf.blade.php          → layout PDF
├── components/
│   ├── button.blade.php | card.blade.php | badge.blade.php | modal.blade.php
│   ├── table.blade.php | input.blade.php | select.blade.php | alert.blade.php
│   ├── avatar.blade.php | empty-state.blade.php | loading.blade.php | pagination.blade.php
├── partials/
│   ├── sidebar.blade.php | header.blade.php | footer.blade.php | notif-bell.blade.php | breadcrumb.blade.php
├── auth/
│   ├── login.blade.php | forgot-password.blade.php | change-password.blade.php
├── dashboard/
│   ├── crew.blade.php | supervisor.blade.php | hrd.blade.php | owner.blade.php
├── attendance/
│   ├── crew-absen.blade.php | crew-riwayat.blade.php | supervisor-hari-ini.blade.php
│   ├── supervisor-detail.blade.php | hrd-verifikasi.blade.php | hrd-detail.blade.php
├── leave/ | salary/ | schedule/ | announcement/ | issue/ | employee/ | user/ | setting/ | report/
├── pdf/
│   ├── rekap-harian.blade.php | rekap-final.blade.php | berita-acara.blade.php
│   ├── slip-gaji.blade.php | rekap-bulanan.blade.php

public/
├── icons/ | manifest.json | sw.js | build/
```

## 5. Layout Utama

### Struktur
```
┌─────────────────────────────────────┐
│  HEADER                             │
│  [Logo] [Judul] [Notif] [Profil ▼]  │
├──────────┬──────────────────────────┤
│          │                          │
│ SIDEBAR  │  CONTENT                 │
│ (menu    │  ┌────────────────────┐  │
│  per     │  │ Breadcrumb         │  │
│  role)   │  ├────────────────────┤  │
│          │  │  Isi halaman       │  │
│          │  └────────────────────┘  │
│          │                          │
├──────────┴──────────────────────────┤
│  FOOTER © 2026 Kafeku               │
└─────────────────────────────────────┘
```

### Header
| Elemen | Fungsi |
|---|---|
| Logo Kafeku | Klik → dashboard |
| Judul halaman | Opsional atau breadcrumb |
| Bell notifikasi | Badge jumlah belum dibaca |
| Avatar + nama | Dropdown: Profil, Ganti Password, Logout |
| Hamburger (HP) | Buka/tutup sidebar |

### Sidebar
- **Dinamis** — menu muncul berdasarkan permission user
- **Grup menu** — Absensi, HR, Laporan, Pengaturan
- **Highlight aktif** — menu yang dibuka berwarna coklat
- **Collapse di laptop** — bisa dikecilkan jadi icon saja
- **Slide-in di HP** — muncul dari kiri, overlay gelap
- **Permission di server** — jangan render menu yang tidak berhak

### Footer
- `© 2026 Kafeku · by AchmatFadilah Dev` | Versi: v1.0

## 6. Halaman per Role

| Role | Halaman |
|---|---|
| Crew | Dashboard, Absen, Riwayat, Cuti, Slip Gaji, Jadwal, Profil, Pengumuman, Issue |
| Supervisor | Dashboard, Absensi Hari Ini, Detail Absen, Approval Cuti, Jadwal, Tukar Shift, Issue, Pengumuman |
| HRD | Dashboard, Verifikasi, Koreksi, Arsip PDF, Karyawan, Cuti, Payroll, Jadwal, Pengumuman, Activity Log |
| Owner | Dashboard, Laporan, Rekap Bulanan, Pengaturan, Kelola User, Activity Log, Payroll Approval |

**Total ±30 halaman** (banyak yang share layout & komponen).

## 7. Komponen Reusable

| Komponen | Fungsi |
|---|---|
| Button | Primary, secondary, success, danger, ghost, size |
| Card | Judul, isi, aksi, badge |
| Badge | Status dengan warna konsisten |
| Table | Header, sortir, pagination, empty state |
| Modal | Konfirmasi, form, detail |
| Input | Text, number, date, time, textarea |
| Select | Dropdown biasa + searchable |
| File Upload | Preview gambar, validasi tipe & ukuran |
| Alert / Toast | Sukses, error, info, warning |
| Empty State | Ilustrasi + teks + tombol aksi |
| Loading | Skeleton / spinner |
| Avatar | Inisial atau foto |
| Breadcrumb | Navigasi posisi |
| Pagination | Untuk tabel besar |
| Confirm Dialog | Sebelum aksi destruktif |
| Date Range Picker | Filter laporan |

## 8. PWA (Progressive Web App)

### File yang Dibutuhkan
| File | Fungsi |
|---|---|
| manifest.json | Nama, icon, warna, display standalone |
| sw.js (Service Worker) | Cache halaman dasar, offline fallback |
| icons/icon-192.png | Icon HP Android |
| icons/icon-512.png | Icon HD |
| icons/apple-touch-icon.png | Icon iPhone |
| offline.html | Halaman fallback saat offline |

### Manifest — Isi Penting
| Field | Nilai |
|---|---|
| name | Kafeku — HRD Coffee Shop |
| short_name | Kafeku |
| start_url | / |
| display | standalone |
| background_color | #F5EFE6 |
| theme_color | #6F4E37 |
| icons | 192 & 512 |

### Service Worker — Strategi
| Halaman | Strategi Cache |
|---|---|
| Login | Network only |
| Dashboard | Network first, fallback cache |
| Absen | Network only (butuh GPS & kamera) |
| Halaman statis | Cache first |
| API data | Network first |

### Install Prompt
- Tampilkan banner "Install Kafeku di HP" setelah 3× kunjungan
- Android: tombol install native
- iPhone: panduan manual "Add to Home Screen"

### Batasan PWA
- Tidak bisa akses GPS di background — absen harus buka app
- Push notification butuh dukungan browser (opsional)
- Kamera butuh HTTPS — wajib deploy HTTPS

## 9. Responsif: Aturan Responsif

### Breakpoint Tailwind
| Breakpoint | Lebar | Target |
|---|---|---|
| default | < 640px | HP |
| sm | ≥ 640px | HP besar / tablet kecil |
| md | ≥ 768px | Tablet |
| lg | ≥ 1024px | Laptop |
| xl | ≥ 1280px | Desktop besar |

### Aturan Layout
| Elemen | HP | Laptop |
|---|---|---|
| Sidebar | Slide-in (hamburger) | Fixed kiri |
| Header | Compact | Full |
| Kartu dashboard | 1 kolom | 2–4 kolom |
| Tabel | Scroll horizontal / kartu | Tabel penuh |
| Form | 1 kolom | 2 kolom (jika perlu) |
| Tombol aksi | Sticky bottom | Inline |
| Modal | Full screen | Centered |

### Aturan Khusus Crew
- Semua tombol utama sticky bottom
- Font minimal 14px
- Input besar — jangan bikin salah tap
- Hindari hover-only — crew pakai touch
- Test di HP asli — bukan hanya browser resize

## 10. Performa Frontend

| Target | Cara |
|---|---|
| First load < 2 detik | Kompres CSS/JS, lazy load gambar |
| Foto absen < 500 KB | Kompres di server sebelum simpan |
| Gambar dashboard lazy load | loading="lazy" |
| Chart ringan | Chart.js minified |
| Cache statis 1 tahun | Header Cache-Control |
| Hindari library besar | Jangan jQuery, moment.js, dll |
| Gunakan Vite | Bundling otomatis Laravel |
| Minify production | npm run build |

## 11. Aksesibilitas (A11y)

| Aturan | Kenapa |
|---|---|
| Kontras teks ≥ 4.5:1 | Mudah dibaca |
| Tombol minimal 44×44px | Jempol-friendly |
| Alt text di gambar | Screen reader |
| Label di form | Jelas |
| Fokus keyboard terlihat | Navigasi keyboard |
| Pesan error jelas | Bahasa manusia |
| Jangan warna saja untuk status | Tambah ikon/teks |

## 12. Notifikasi Frontend

### In-App (Bell)
- Badge jumlah belum dibaca
- Dropdown 5 notifikasi terakhir
- Klik → halaman detail
- "Tandai semua dibaca"

### Toast
- Muncul 3 detik, auto hilang
- Posisi: atas (HP), kanan atas (laptop)
- Jenis: sukses (hijau), error (merah), info (biru)

### Modal / Popup
- Untuk konfirmasi aksi penting
- Untuk pengumuman urgent

### Push (Opsional)
- Butuh service worker + VAPID key
- Izin user wajib

## 13. Kriteria Selesai Frontend

| # | Kriteria |
|---|---|
| 1 | Layout + sidebar dinamis jalan per role |
| 2 | Login page rapi + logo Kafeku |
| 3 | Dashboard crew mobile-first |
| 4 | Halaman absen: kamera + GPS + overlay |
| 5 | Tombol sticky bottom di HP |
| 6 | Badge status konsisten semua halaman |
| 7 | Komponen reusable dipakai konsisten |
| 8 | Tabel bisa sortir & filter |
| 9 | Modal konfirmasi sebelum aksi penting |
| 10 | Toast sukses/error muncul |
| 11 | PWA installable di Android & iPhone |
| 12 | Loading < 2 detik di 3G |
| 13 | Responsive: HP, tablet, laptop |
| 14 | Aksesibel: kontras, tombol besar, label |
| 15 | Uji di HP asli (Android + iPhone) |

## 14. Urutan Pengerjaan Frontend

| Langkah | Yang Dikerjakan |
|---|---|
| 1 | Setup Tailwind + Flowbite + font |
| 2 | Design system di tailwind.config.js |
| 3 | Layout utama (header + sidebar dinamis + footer) |
| 4 | Komponen reusable (button, card, badge, table, modal, alert, dll) |
| 5 | Halaman login + ganti password |
| 6 | Dashboard crew + halaman absen |
| 7 | Dashboard supervisor + halaman validasi |
| 8 | Dashboard HRD + halaman verifikasi |
| 9 | Dashboard owner + laporan |
| 10 | PWA (manifest, service worker, icon) |
| 11 | PDF template (Blade → DomPDF) |
| 12 | Uji di HP Android + iPhone |

---

# 📱 WIREFRAME — Halaman Absen Crew (HP)

Halaman paling kritis — dipakai 2× sehari oleh crew. Harus cepat, jelas, tahan sinyal jelek, tidak bisa diakali.

## 15. Peta State Halaman Absen

```
[Masuk Halaman]
      ↓
[Cek Status Hari Ini]
      ├── Sudah absen masuk & pulang → State "Selesai"
      ├── Sudah absen masuk → State "Absen Pulang"
      └── Belum absen → State "Absen Masuk"
              ↓
      [Cek GPS]
              ├── GPS off → State "GPS Mati"
              ├── GPS loading → State "Mencari GPS"
              ├── Di luar radius → State "Di Luar Radius"
              └── Dalam radius → State "Siap Absen"
                      ↓
              [Aktifkan Kamera]
                      ├── Izin ditolak → State "Kamera Ditolak"
                      ├── Kamera error → State "Kamera Error"
                      └── Kamera siap → State "Ambil Foto"
                              ↓
                      [Foto Diambil] → State "Preview Foto"
                              ↓
                      [Kirim] → State "Mengirim"
                              ↓
                      ├── Sukses → State "Sukses"
                      └── Gagal → State "Gagal Kirim"
```

**Total 11 state.** Semua harus jelas.

## 16. State: Absen Masuk (Awal)

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
├─────────────────────────────────┤
│  Rabu, 11 November 2026         │
│  08:03 WIB                      │
├─────────────────────────────────┤
│  ┌───────────────────────────┐  │
│  │      [PETA KECIL]         │  │
│  │   ● Anda   ● Kafe (85m)   │  │
│  │   ⭕ radius 100m          │  │
│  └───────────────────────────┘  │
│                                 │
│  📍 Lokasi: Dalam radius        │
│  🎯 Akurasi: 8 meter            │
├─────────────────────────────────┤
│                                 │
│  ┌───────────────────────────┐  │
│  │                           │  │
│  │    [KAMERA SELFIE]        │  │
│  │    (live view)            │  │
│  │                           │  │
│  └───────────────────────────┘  │
│                                 │
│  [   📷  AMBIL FOTO   ]         │
│                                 │
├─────────────────────────────────┤
│  [   ABSEN MASUK   ]            │
│   (disabled sampai foto ada)    │
└─────────────────────────────────┘
```

### Komponen
| Komponen | Fungsi |
|---|---|
| Header | Tombol back + judul |
| Jam real-time | Jam server, update tiap detik |
| Peta kecil | Posisi crew + kafe + radius |
| Status GPS | Teks + ikon: dalam radius / luar / error |
| Preview kamera | Live view kamera depan |
| Tombol ambil foto | Besar, tengah, mudah dijangkau |
| Tombol kirim | Sticky bottom, disabled sampai foto ada |

### Aturan UX
- Tombol kirim disabled kalau GPS belum OK atau foto belum ada
- Warna tombol: abu-abu (disabled) → coklat (aktif)
- Peta kecil tidak perlu interaktif
- Jam pakai server, bukan jam HP
- Auto-scroll ke tombol kalau layar kecil

### Feedback
- ✅ "Lokasi terdeteksi · Dalam radius"
- ⏳ "Mencari lokasi..."
- ❌ "Lokasi di luar radius"

## 17. State: Mencari GPS (Loading)

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
├─────────────────────────────────┤
│                                 │
│         ⏳ (spinner)            │
│                                 │
│     Mencari lokasi Anda...      │
│                                 │
│   Pastikan GPS aktif dan        │
│   Anda berada di area kafe      │
│                                 │
│                                 │
│  [   Coba Lagi   ]              │
│                                 │
└─────────────────────────────────┘
```

### Aturan UX
- Spinner minimal 1 detik, maksimal 10 detik
- Setelah 10 detik → tombol "Coba Lagi"
- Setelah 30 detik → "GPS tidak merespons"
- Jangan biarkan crew menunggu tanpa kepastian

## 18. State: GPS Mati / Izin Ditolak

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
├─────────────────────────────────┤
│                                 │
│         📍 (ikon besar)         │
│                                 │
│   Lokasi Tidak Aktif            │
│                                 │
│   Kafeku butuh lokasi Anda      │
│   untuk memastikan absen        │
│   dilakukan di coffee shop.     │
│                                 │
│   1. Buka Pengaturan HP         │
│   2. Cari "Lokasi" atau "GPS"   │
│   3. Aktifkan                   │
│   4. Kembali ke Kafeku          │
│                                 │
│  [   Buka Pengaturan   ]        │
│  [   Coba Lagi   ]              │
│                                 │
├─────────────────────────────────┤
│  (tombol kirim tidak muncul)    │
└─────────────────────────────────┘
```

### Aturan UX
- Jelaskan kenapa GPS dibutuhkan
- Panduan singkat 3–4 langkah
- Tombol "Buka Pengaturan" — deep link ke setting HP
- Tombol "Coba Lagi" — setelah aktifkan GPS
- Tidak ada tombol absen — tidak bisa dilanjutkan tanpa GPS

## 19. State: Di Luar Radius

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
├─────────────────────────────────┤
│  ┌───────────────────────────┐  │
│  │      [PETA]               │  │
│  │   ● Anda   ● Kafe         │  │
│  │   │ 250m                  │  │
│  │   ⭕ radius 100m          │  │
│  └───────────────────────────┘  │
│                                 │
│         ⚠️                      │
│                                 │
│   Anda di Luar Radius           │
│                                 │
│   Jarak: 250 meter              │
│   Radius maksimal: 100 meter    │
│                                 │
│   Mendekatlah ke coffee shop    │
│   untuk melakukan absen.        │
│                                 │
│  [   Coba Lagi   ]              │
│                                 │
├─────────────────────────────────┤
│  (tombol kirim tidak muncul)    │
└─────────────────────────────────┘
```

### Aturan UX
- Tampilkan jarak aktual dan radius
- Garis di peta visual jarak crew ke kafe
- Warna merah — jelas ini error
- Tombol "Coba Lagi" — refresh GPS
- Tidak bisa dilanjutkan — tidak ada tombol absen
- Pesan suportif, tidak menghakimi

## 20. State: Siap Absen (GPS + Kamera OK)

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
│  Rabu, 11 Nov 2026 · 08:03 WIB  │
├─────────────────────────────────┤
│  ┌───────────────────────────┐  │
│  │  ● Anda      ● Kafe 85m   │  │
│  │  ⭕ Dalam radius ✓        │  │
│  └───────────────────────────┘  │
│                                 │
│  ┌───────────────────────────┐  │
│  │                           │  │
│  │    [KAMERA SELFIE]        │  │
│  │                           │  │
│  │   ┌─────────────────────┐ │  │
│  │   │ Kafeku              │ │  │
│  │   │ 08:03 · 11 Nov 2026 │ │  │
│  │   │ -6.123, 106.456     │ │  │
│  │   │ Budi Santoso        │ │  │
│  │   └─────────────────────┘ │  │
│  │                           │  │
│  └───────────────────────────┘  │
│                                 │
│  [   📷  AMBIL FOTO   ]         │
│                                 │
├─────────────────────────────────┤
│  [   ABSEN MASUK   ]            │
│   (disabled — foto belum ada)   │
└─────────────────────────────────┘
```

### Overlay di kamera (preview)
- Logo Kafeku — pojok kiri atas
- Jam + tanggal — kanan atas
- Koordinat GPS — kiri bawah
- Nama crew — kanan bawah

### Aturan UX
- Overlay tampil di preview
- Kamera depan default — selfie
- Tombol ambil foto besar — 64px tinggi minimal
- Tombol kirim masih disabled — sampai foto diambil
- Auto-focus kalau memungkinkan

## 21. State: Preview Foto (Setelah Ambil)

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
│  Rabu, 11 Nov 2026 · 08:03 WIB  │
├─────────────────────────────────┤
│  ┌───────────────────────────┐  │
│  │   [FOTO HASIL SELFIE]     │  │
│  │   + overlay               │  │
│  │   ✓ Wajah terlihat jelas  │  │
│  └───────────────────────────┘  │
│                                 │
│  📍 Dalam radius (85m)          │
│  🕐 08:03 WIB                   │
│                                 │
│  [   🔄  ULANGI FOTO   ]        │
│                                 │
├─────────────────────────────────┤
│  [   KIRIM ABSEN   ]            │ ← aktif, coklat
│                                 │
└─────────────────────────────────┘
```

### Aturan UX

## 22. State: Mengirim

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
├─────────────────────────────────┤
│                                 │
│         ⏳ (spinner)            │
│                                 │
│     Mengirim absen...           │
│                                 │
│     ████████░░░░ 60%            │
│                                 │
│   Jangan tutup halaman ini      │
│                                 │
└─────────────────────────────────┘
```

### Aturan UX
- Full screen loading — tidak bisa back
- Progress bar kalau upload > 1 detik
- Tombol back disabled — cegah batal tidak sengaja
- Timeout 30 detik
- Tidak boleh double-submit

## 23. State: Sukses

```
┌─────────────────────────────────┐
│                                 │
│         ✅                      │
│                                 │
│   Absen Berhasil!               │
│                                 │
│   Masuk: 08:03 WIB              │
│   Lokasi: Dalam radius (85m)    │
│                                 │
│   ┌─────────────────────────┐  │
│   │  [FOTO THUMBNAIL]       │  │
│   └─────────────────────────┘  │
│                                 │
│   Absen akan divalidasi         │
│   oleh supervisor.              │
│                                 │
│  [   KEMBALI KE DASHBOARD   ]   │
│  [   Lihat Riwayat Absen    ]   │
│                                 │
└─────────────────────────────────┘
```

### Aturan UX
- Hijau besar — jelas sukses
- Ringkasan — jam & lokasi
- Thumbnail foto — konfirmasi visual
- Info selanjutnya — "akan divalidasi supervisor"
- Tombol utama — kembali ke dashboard
- Link sekunder — lihat riwayat
- Tidak bisa back ke form
- Foto besar — crew bisa cek wajah jelas
- Tombol ulangi — kalau foto jelek
- Tombol kirim aktif — coklat, jelas
- Info ringkas — GPS + jam

## 24. State: Gagal Kirim

```
┌─────────────────────────────────┐
│ ←  Absen Masuk                  │
├─────────────────────────────────┤
│                                 │
│         ❌                      │
│                                 │
│   Gagal Mengirim Absen          │
│                                 │
│   Penyebab:                     │
│   • Koneksi internet terputus   │
│                                 │
│   Foto dan data absen Anda      │
│   masih tersimpan.              │
│   Silakan coba lagi.            │
│                                 │
│  [   COBA LAGI   ]              │
│  [   Ambil Foto Ulang   ]       │
│                                 │
└─────────────────────────────────┘
```

### Aturan UX
- Jelaskan penyebab — bukan "Error 500"
- Data tidak hilang — foto & GPS disimpan sementara
- Tombol "Coba Lagi" — kirim ulang
- Tombol "Ambil Foto Ulang" — ganti foto
- Retry otomatis 3× dengan jeda 2 detik

## 25. State: Absen Pulang

Sama seperti absen masuk, tapi:
- Judul: "Absen Pulang"
- Info tambahan: Ringkasan jam masuk
- Tombol: "Absen Pulang"
- Setelah sukses: Tampilkan durasi total kerja

| Aspek | Masuk | Pulang |
|---|---|---|
| Judul | Absen Masuk | Absen Pulang |
| Info tambahan | — | Ringkasan jam masuk |
| Tombol | Absen Masuk | Absen Pulang |
| Validasi | Radius + foto | Radius + foto + sudah masuk |
| Setelah sukses | "Absen masuk berhasil" | "Absen pulang berhasil · Total kerja 9j 2m" |

### Aturan UX
- Info masuk ditampilkan — crew ingat jam masuk
- Total jam kerja — setelah pulang sukses
- Sama persis dengan alur masuk — konsisten

## 26. State: Sudah Selesai (Masuk + Pulang)

```
┌─────────────────────────────────┐
│ ←  Absensi Hari Ini             │
├─────────────────────────────────┤
│                                 │
│         ✅                      │
│                                 │
│   Absensi Hari Ini Selesai      │
│                                 │
│   ┌─────────────────────────┐  │
│   │ Masuk   08:03 WIB       │  │
│   │ Pulang  17:05 WIB       │  │
│   │ Durasi  9 jam 2 menit   │  │
│   └─────────────────────────┘  │
│                                 │
│   Status: Menunggu validasi     │
│   supervisor                    │
│                                 │
│   ┌─────────────────────────┐  │
│   │  [FOTO MASUK]           │  │
│   └─────────────────────────┘  │
│   ┌─────────────────────────┐  │
│   │  [FOTO PULANG]          │  │
│   └─────────────────────────┘  │
│                                 │
│  [   LIHAT RIWAYAT ABSEN   ]    │
│                                 │
└─────────────────────────────────┘
```

### Aturan UX
- Ringkasan lengkap — masuk, pulang, durasi
- Status validasi — transparan
- Thumbnail foto — konfirmasi
- Tidak ada tombol absen — sudah selesai

## 27. Interaksi Kunci

### Izin GPS
| Kondisi | Aksi |
|---|---|
| Belum minta izin | Minta izin otomatis saat buka halaman |
| Izin ditolak | Tampilkan panduan + tombol "Coba Lagi" |
| Izin diberikan | Lanjut ambil GPS |

### Izin Kamera
| Kondisi | Aksi |
|---|---|
| Belum minta izin | Minta izin saat tap "Ambil Foto" |
| Izin ditolak | Tampilkan panduan + tombol "Buka Pengaturan" |
| Izin diberikan | Aktifkan kamera |

### Tombol Kirim
| Kondisi | Status |
|---|---|
| GPS belum OK | Disabled (abu-abu) |
| Foto belum ada | Disabled |
| GPS OK + foto ada | Aktif (coklat) |
| Sedang kirim | Loading (spinner) |
| Sukses | Hilang (redirect) |

## 28. Pesan Error (Bahasa Manusia)

| Kondisi | Pesan ke Crew |
|---|---|
| GPS off | Lokasi tidak aktif. Aktifkan GPS di pengaturan HP. |
| GPS timeout | Lokasi tidak terdeteksi. Coba lagi di area terbuka. |
| Luar radius | Anda 250m dari kafe. Mendekatlah untuk absen. |
| Kamera ditolak | Kamera tidak aktif. Izinkan akses kamera untuk absen. |
| Kamera error | Kamera tidak bisa dibuka. Coba tutup dan buka lagi. |
| Upload gagal | Gagal mengirim. Cek koneksi internet Anda. |
| Server error | Server sedang sibuk. Coba lagi sebentar. |
| Sudah absen | Anda sudah absen masuk hari ini. |
| Belum absen masuk | Absen masuk dulu sebelum absen pulang. |
| Radius belum diatur | Lokasi kafe belum diatur. Hubungi HRD. |

**Aturan:** jangan tampilkan kode error teknis ke crew.

## 29. Perilaku Khusus HP

| Platform | Catatan |
|---|---|
| iPhone | Kamera butuh HTTPS, PWA push terbatas, Add to Home Screen manual |
| Android | Kamera lebih fleksibel, GPS kurang akurat di dalam, install native |
| Umum | HTTPS wajib, test di HP asli, kamera depan default, lock portrait |

## 30. Aksesibilitas Halaman Absen

| Aturan | Kenapa |
|---|---|
| Tombol minimal 48px | Jempol-friendly |
| Kontras teks ≥ 4.5:1 | Mudah dibaca |
| Font minimal 14px | Crew mungkin tidak muda |
| Ikon + teks | Bukan warna saja |
| Label di form | Jelas |

## 31. Performa Halaman Absen

| Target | Cara |
|---|---|
| Halaman buka < 1 detik | Lazy load peta, kamera setelah GPS |
| GPS < 5 detik | Cache posisi terakhir, refresh |
| Kamera aktif < 2 detik | Preload permission |
| Upload foto < 5 detik | Kompres foto di HP sebelum kirim |
| Total absen < 30 detik | Dari buka sampai sukses |

## 32. Checklist Implementasi Frontend Absen

| # | Item | Prioritas |
|---|---|---|
| 1 | Layout halaman (header, peta, kamera, tombol) | Wajib |
| 2 | Peta Leaflet + pin + radius | Wajib |
| 3 | GPS API + validasi radius | Wajib |
| 4 | Kamera API (MediaDevices) | Wajib |
| 5 | Overlay di preview kamera | Wajib |
| 6 | State loading GPS | Wajib |
| 7 | State luar radius | Wajib |
| 8 | State GPS mati | Wajib |
| 9 | State kamera ditolak | Wajib |
| 10 | Preview foto + ulangi | Wajib |
| 11 | Kirim + loading | Wajib |
| 12 | State sukses | Wajib |
| 13 | State gagal + retry | Wajib |
| 14 | Absen pulang (varian) | Wajib |
| 15 | State selesai | Wajib |
| 16 | Pesan error bahasa manusia | Penting |
| 17 | Kompres foto sebelum kirim | Penting |
| 18 | Uji di HP asli Android + iPhone | Wajib |