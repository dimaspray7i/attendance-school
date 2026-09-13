<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// PERHATIAN: Nama class harus SchoolClass, bukan Major
class SchoolClass extends Model
{
    use HasFactory;

    /**
     * Override nama tabel karena model bernama SchoolClass 
     * tetapi tabel di database bernama 'classes'
     */
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'major_id',
        'grade',
        'description',
    ];

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}