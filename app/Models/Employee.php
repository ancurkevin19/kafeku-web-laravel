<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Employee extends Authenticatable
{
    use HasApiTokens, HasRoles, HasPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nomor_pegawai',
        'jabatan',
        'photo',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Scope untuk karyawan aktif saja
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessor for full name
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    // Mutator for photo URL
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo && Storage::exists('employees/' . $this->photo)) {
            return Storage::url('employees/' . $this->photo);
        }
        return asset('img/placeholder.png');
    }

    // Relationship: Attendances
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class)->latest('tanggal');
    }

    // Relationship: Attendance corrections
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }
}