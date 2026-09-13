<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'status',
        'overall_quality_score',
        'sample_count',
        'approved_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'admin_notes',
        'enrolled_at',
        'disabled_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_quality_score' => 'decimal:4',
            'sample_count' => 'integer',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function embeddings()
    {
        return $this->hasMany(FaceEmbedding::class);
    }

    public function logs()
    {
        return $this->hasMany(FaceEnrollmentLog::class);
    }

    public function primaryEmbedding()
    {
        return $this->hasOne(FaceEmbedding::class)->where('is_primary', true);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    // Status helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function needsReEnroll(): bool
    {
        return $this->status === 're_enroll';
    }

    public function isDisabled(): bool
    {
        return $this->status === 'disabled';
    }
}