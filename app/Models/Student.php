<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'class_id',
        'academic_year_id',
        'full_name',
        'nickname',
        'gender',
        'birth_date',
        'address',
        'phone',
        'photo_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

        public function parents()
    {
        return $this->belongsToMany(ParentProfile::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function faceProfile()
    {
        return $this->hasOne(FaceProfile::class);
    }

    public function faceEnrollmentLogs()
    {
        return $this->hasMany(FaceEnrollmentLog::class);
    }
}