<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status_masuk',
        'status_pulang',
        'lokasi_masuk_lat',
        'lokasi_masuk_lng',
        'lokasi_pulang_lat',
        'lokasi_pulang_lng',
        'jarak_masuk_meter',
        'jarak_pulang_meter',
        'foto_masuk',
        'foto_pulang',
        'keterangan',
        'validated_by',
        'validated_at',
        'verification_by',
        'verification_at',
        'status_validasi',
        'status_verifikasi',
        'is_corrected',
        'corrected_by',
        'corrected_at',
    ];

    protected $casts = [
        'status_masuk' => 'string',
        'status_pulang' => 'string',
        'status_validasi' => 'string',
        'status_verifikasi' => 'string',
        'is_corrected' => 'boolean',
        'jam_masuk' => 'datetime:H:i',
        'jam_pulang' => 'datetime:H:i',
    ];

    protected $guarded = [];

    // Relation ke Employee
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Activity log creation
    public function logActivity(string $action, string $description = ''): void
    {
        // Log activity ke sistem
        \Log::channel('activity') -> info(
            'Attendance [' . $this->id . '] - ' . $action . ': ' . $description,
            [
                'attendance_id' => $this->id,
                'employee_id' => $this->employee_id,
                'tanggal' => $this->tanggal,
                'action' => $action,
                'description' => $description,
                'created_at' => now(),
            ]
        );
    }

    // Status helper methods
    public function getStatusMasukLabelAttribute(): string
    {
        return ucfirst($this->status_masuk);
    }

    public function getStatusPulangLabelAttribute(): string
    {
        return ucfirst($this->status_pulang);
    }

    public function isTerlambat(): bool
    {
        if (!$this->jam_masuk || !$this->setting->jam_masuk) {
            return false;
        }
        $jamMasuk = \Carbon\Carbon::parse($this->jam_masuk);
        $settingJamMasuk = \Carbon\Carbon::parse($this->setting->jam_masuk);
        $selisihMenit = $jamMasuk->diffInMinutes($settingJamMasuk);
        
        return $selisihMenit > $this->setting->toleransi_terlambat_menit;
    }

    // Getter for formatted timestamp
    public function getCreatedAtFormatAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d M Y H:i') : '-';
    }
}