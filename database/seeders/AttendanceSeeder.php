<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create attendance permissions
        $permissions = [
            'attendance.view.self',
            'attendance.view.all',
            'attendance.create',
            'attendance.validate',
            'attendance.verify',
            'attendance.correct',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create attendance roles and assign permissions
        $rolePermissions = [
            'crew' => ['attendance.view.self', 'attendance.create', 'attendance.view.self'],
            'supervisor' => ['attendance.view.all', 'attendance.validate'],
            'hrd' => ['attendance.view.all', 'attendance.verify', 'attendance.correct'],
            'owner' => ['attendance.view.all'],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::create(['name' => $roleName, 'guard_name' => 'web']);
            foreach ($perms as $perm) {
                $role->givePermissionTo($perm);
            }
        }

        // Create default attendance settings
        \App\Models\AttendanceSetting::create([
            'office_lat' => -6.200000,
            'office_lng' => 106.833333,
            'office_radius_meter' => 250,
            'jam_masuk' => '08:00:00',
            'jam_pulang' => '17:00:00',
            'toleransi_terlambat_menit' => 15,
            'wajib_selfie' => true,
            'wajib_gps' => true,
        ]);
    }
}