<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::create([
            'name' => '2025/2026',
            'start_year' => 2025,
            'end_year' => 2026,
            'is_active' => true,
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'description' => 'Tahun ajaran aktif',
        ]);

        AcademicYear::create([
            'name' => '2026/2027',
            'start_year' => 2026,
            'end_year' => 2027,
            'is_active' => false,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'description' => 'Tahun ajaran berikutnya',
        ]);
    }
}