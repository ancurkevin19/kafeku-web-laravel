<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $table = 'attendance_corrections';

    protected $fillable = [
        'attendance_id',
        'field',
        'nilai_lama',
        'nilai_baru',
        'alasan',
        'corrected_by',
        'created_at',
    ];

    // Relation ke Attendance
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // Relation ke User (who corrected)
    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'corrected_by');
    }
}