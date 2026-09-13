<?php

namespace App\Policies;

use App\Models\FaceProfile;
use App\Models\User;

class FaceProfilePolicy
{
    /**
     * Admin bisa melihat semua face profile.
     * Siswa hanya bisa melihat profile miliknya.
     */
    public function view(User $user, FaceProfile $faceProfile): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSiswa() && $user->student) {
            return $faceProfile->student_id === $user->student->id;
        }

        return false;
    }

    /**
     * Hanya admin yang bisa review (approve/reject).
     */
    public function review(User $user, FaceProfile $faceProfile): bool
    {
        return $user->isAdmin();
    }

    /**
     * Siswa bisa melakukan enrollment untuk dirinya sendiri.
     */
    public function enroll(User $user): bool
    {
        return $user->isSiswa() && $user->student !== null;
    }
}