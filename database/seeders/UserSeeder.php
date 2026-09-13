<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk membuat 3 user: 1 admin, 1 siswa, 1 orang tua.
     */
    public function run(): void
    {
        // Pastikan data master yang dibutuhkan sudah ada
        $academicYear = AcademicYear::active()->first();
        if (!$academicYear) {
            $this->command->error('Tahun ajaran aktif tidak ditemukan. Jalankan AcademicYearSeeder terlebih dahulu.');
            return;
        }

        $schoolClass = SchoolClass::first();
        if (!$schoolClass) {
            $this->command->error('Kelas tidak ditemukan. Pastikan data kelas sudah ada.');
            return;
        }

        DB::transaction(function () use ($academicYear, $schoolClass) {

            // =================================================================
            // 1. ADMIN
            // =================================================================
            $adminUser = User::create([
                'name' => 'Administrator Sekolah',
                'email' => 'admin@sekolah.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->info("✓ Admin created: {$adminUser->email}");

            // =================================================================
            // 2. SISWA
            // =================================================================
            $studentUser = User::create([
                'name' => 'Ahmad Fauzan',
                'email' => 'siswa@sekolah.id',
                'password' => Hash::make('password'),
                'role' => 'siswa',
                'email_verified_at' => now(),
            ]);

            $student = Student::create([
                'user_id' => $studentUser->id,
                'nis' => '2024001',
                'nisn' => '0012345678',
                'full_name' => 'Ahmad Fauzan',
                'nickname' => 'Ahmad',
                'gender' => 'L',
                'birth_date' => '2009-05-15',
                'class_id' => $schoolClass->id,
                'academic_year_id' => $academicYear->id,
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'phone' => '081234567890',
                'status' => 'active',
            ]);

            $this->command->info("✓ Siswa created: {$studentUser->email} (NIS: {$student->nis})");

            // =================================================================
            // 3. ORANG TUA
            // =================================================================
            $parentUser = User::create([
                'name' => 'Budi Hartono',
                'email' => 'ortu@sekolah.id',
                'password' => Hash::make('password'),
                'role' => 'orang_tua',
                'email_verified_at' => now(),
            ]);

            $parent = ParentProfile::create([
                'user_id' => $parentUser->id,
                'full_name' => 'Budi Hartono',
                'relationship' => 'ayah',
                'phone' => '081298765432',
                'email' => 'ortu@sekolah.id',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'occupation' => 'Wiraswasta',
            ]);

            // Hubungkan orang tua dengan siswa
            $parent->students()->attach($student->id);

            $this->command->info("✓ Orang Tua created: {$parentUser->email} (Anak: {$student->full_name})");
        });
    }
}