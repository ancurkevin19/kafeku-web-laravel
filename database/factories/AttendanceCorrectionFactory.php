<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\AttendanceCorrection;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(AttendanceCorrection::class, function (Faker $faker) {
    return [
        'attendance_id' => rand(1, 1000),
        'field' => $faker->randomElement(['jam_masuk', 'jam_pulang', 'lokasi_masuk_lat', 'lokasi_masuk_lng', 'lokasi_pulang_lat', 'lokasi_pulang_lng', 'keterangan']),
        'nilai_lama' => $faker->randomElement(['08:00:00', '09:30:00', 'Jakarta', '106.8', 'some value']),
        'nilai_baru' => $faker->randomElement(['08:30:00', '10:00:00', 'Surabaya', '105.5', 'nilai baru']),
        'alasan' => $faker->sentence(5),
        'corrected_by' => rand(1, 50),
        'created_at' => now(),
    ];
});