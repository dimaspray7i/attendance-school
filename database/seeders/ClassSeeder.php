<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $majors = Major::all();

        if ($majors->isEmpty()) {
            $this->command->error('Jurusan tidak ditemukan. Jalankan MajorSeeder terlebih dahulu.');
            return;
        }

        $count = 0;
        foreach ($majors as $major) {
            for ($grade = 10; $grade <= 12; $grade++) {
                for ($section = 1; $section <= 2; $section++) {
                    $gradeName = match($grade) {
                        10 => 'X',
                        11 => 'XI',
                        12 => 'XII',
                    };

                    $name = "{$gradeName} {$major->code} {$section}";
                    $code = "{$grade}{$major->code}{$section}";

                    SchoolClass::firstOrCreate(
                        ['code' => $code],
                        [
                            'name' => $name,
                            'major_id' => $major->id,
                            'grade' => $grade,
                            'description' => "Kelas {$name} - {$major->name}",
                        ]
                    );
                    $count++;
                }
            }
        }

        $this->command->info("✓ {$count} kelas berhasil dibuat");
    }
}