<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Major;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $majors = [
            [
                'name' => 'Rekayasa Perangkat Lunak',
                'code' => 'RPL',
                'description' => 'Jurusan pengembangan perangkat lunak',
            ],
            [
                'name' => 'Teknik Komputer dan Jaringan',
                'code' => 'TKJ',
                'description' => 'Jurusan jaringan dan infrastruktur komputer',
            ],
            [
                'name' => 'Multimedia',
                'code' => 'MM',
                'description' => 'Jurusan desain dan multimedia',
            ],
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }
    }
}