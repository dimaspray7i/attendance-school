<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FaceEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'face_profile_id',
        'sample_type',
        'embedding_data',
        'embedding_dimension',
        'quality_score',
        'image_path',
        'is_primary',
        'captured_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'embedding_data' => 'array', // JSON otomatis di-decode
            'embedding_dimension' => 'integer',
            'quality_score' => 'decimal:4',
            'is_primary' => 'boolean',
            'captured_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected $hidden = [
        'embedding_data', // JANGAN expose embedding ke client/view
    ];

    public function faceProfile()
    {
        return $this->belongsTo(FaceProfile::class);
    }

    public function getFullImagePathAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        return Storage::disk('local')->path($this->image_path);
    }

    public function getImageUrlAttribute(): ?string
    {
        // Hanya untuk admin yang berwenang, gunakan route khusus
        if (!$this->image_path) {
            return null;
        }
        return route('admin.face-profiles.image', $this->id);
    }

    public function getEmbeddingVector(): array
    {
        return $this->embedding_data ?? [];
    }
}