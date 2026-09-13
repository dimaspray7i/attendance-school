<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceEnrollmentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'face_profile_id',
        'action',
        'performed_by',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function faceProfile()
    {
        return $this->belongsTo(FaceProfile::class);
    }

    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}