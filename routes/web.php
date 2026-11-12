<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Attendance\{
    AttendanceMasukController,
    AttendanceSayaController,
    AbsensiPulangController,
    ValidasiAbsensiController,
    VerifikasiAbsensiController,
    KoreksiAbsensiController,
    LaporanAbsensiController,
    NotifikasiController,
    OwnerDashboardController
};
use App\Http\Controllers\Api\AttendanceApi;
use App\Http\Controllers\Api\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the Laravel ServiceProvider within a group which
| is assigned the "web" middleware group. Enjoy building the application!
|
*/

// Authentication routes (Laravel Breeze default)
Auth::routes(['verify' => true]);

// Home route
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ============================================================
// ATTENDANCE ROUTES - Block 2: Absensi
// ============================================================

// Crew Routes
Route::middleware(['auth', 'role:crew'])->group(function () {
    // Absensi Masuk
    Route::get('/absensi-masuk', [AttendanceMasukController::class, 'index'])->name('absensi.masuk.index');
    Route::post('/absensi-masuk', [AttendanceMasukController::class, 'absenMasuk'])->name('absensi.masuk');
    
    // Absensi Pulang
    Route::get('/absensi-pulang', [AbsensiPulangController::class, 'index'])->name('absensi.pulang.index');
    Route::post('/absensi-pulang', [AbsensiPulangController::class, 'absenPulang'])->name('absensi.pulang');
    
    // Absensi Saya (riwayat personal)
    Route::get('/absensi-saya', [AttendanceSayaController::class, 'index'])->name('absensi.saya');
});

// Supervisor Routes
Route::middleware(['auth', 'role:supervisor'])->group(function () {
    // Validasi Absensi Tim
    Route::get('/validasi-absensi', [ValidasiAbsensiController::class, 'index'])->name('validasi.absensi');
    Route::post('/validasi-absensi/{id}', [ValidasiAbsensiController::class, 'validasiAbsensi'])->name('validasi.absensi.aksi');
    
    // View team attendance (read-only)
    Route::get('/absensi-tim', [ValidasiAbsensiController::class, 'teamAttendance'])->name('absensi.tim');
});

// HRD Routes
Route::middleware(['auth', 'role:hrd'])->group(function () {
    // Verifikasi Absensi
    Route::get('/verifikasi-absensi', [VerifikasiAbsensiController::class, 'index'])->name('verifikasi.absensi');
    Route::post('/verifikasi-absensi/{id}', [VerifikasiAbsensiController::class, 'verifikasiAbsensi'])->name('verifikasi.absensi.aksi');
    
    // Koreksi Absensi
    Route::get('/koreksi-absensi', [KoreksiAbsensiController::class, 'index'])->name('koreksi.absensi');
    Route::post('/koreksi-absensi', [KoreksiAbsensiController::class, 'simpanKoreksi'])->name('koreksi.absensi.aksi');
    
    // View all attendance
    Route::get('/absensi-semua', [VerifikasiAbsensiController::class, 'allAttendance'])->name('absensi.semua');
});

// Owner Routes
Route::middleware(['auth', 'role:owner'])->group(function () {
    // Owner Dashboard
    Route::get('/owner-dashboard', [OwnerDashboardController::class, 'index'])->name('owner.dashboard');
    
    // Laporan Absensi
    Route::get('/laporan-absensi', [LaporanAbsensiController::class, 'index'])->name('laporan.absensi');
    Route::post('/laporan-absensi/generate', [LaporanAbsensiController::class, 'generatePDF'])->name('laporan.absensi.generate');
    
    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/baca/{id}', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    Route::post('/notifikasi/kirim-whatsapp', [NotifikasiController::class, 'kirimWhatsApp'])->name('notifikasi.whatsapp');
});

// ============================================================
// API ROUTES - For Mobile App / Livewire
// ============================================================

Route::middleware(['auth:api', 'verified'])->prefix('api')->group(function () {
    // Attendance API
    Route::get('/attendance/settings', [AttendanceApi::class, 'getSettings'])->name('api.attendance.settings');
    Route::post('/attendance/settings', [AttendanceApi::class, 'updateSettings'])->name('api.attendance.settings.update');
    
    // GPS/Camera endpoints
    Route::post('/attendance/validate-gps', [AttendanceApi::class, 'validateGPS'])->name('api.attendance.validate-gps');
    Route::post('/attendance/upload-foto', [AttendanceApi::class, 'uploadFoto'])->name('api.attendance.upload-foto');
    
    // Activity log
    Route::get('/activity/log', [AttendanceApi::class, 'getActivityLog'])->name('api.activity.log');
});

// ============================================================
// ROUTE GROUPS
// ============================================================

// Route group for attendance-related routes with permission checks
Route::middleware(['auth'])->group(function () {
    // Pastikan hanya role yang tepat yang bisa akses
    Route::get('/profil', function () {
        return view('profil');
    })->name('profil');
});

// Default redirect after login
Route::get('/redirect-after-login', function () {
    return redirect()->intended('/home');
})->name('redirect.after.login');
