<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use App\Models\User;

class AttendanceCorrectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data attendance yang ada
        $attendances = Attendance::all();

        // Ambil user IDs untuk corrected_by
        $userIds = User::pluck('id')->toArray();

        // Generate 30-50 data koreksi acak
        $faker = \Faker\Faker::create('id_ID');

        for ($i = 0; $i <= 50; $i++) {
            $attendance = $attendances->random();

            AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'field' => $faker->randomElement(['jam_masuk', 'jam_pulang', 'keterangan', 'lokasi_masuk_lat']),
                'nilai_lama' => match($faker->randomElement(['hour', 'text', 'coord'])) {
                    'hour' => $faker->time('H:i:s'),
                    'text' => $faker->sentence(3),
                    'coord' => $faker->latitude . ',' . $faker->longitude,
                },
                'nilai_baru' => match($faker->randomElement(['hour', 'text', 'coord'])) {
                    'hour' => $faker->time('H:i:s'),
                    'text' => $faker->sentence(3),
                    'coord' => $faker->latitude . ',' . $faker->longitude,
                },
                'alasan' => $faker->sentence(8),
                'corrected_by' => $faker->randomElement($userIds),
            ]);
        }
    }
}