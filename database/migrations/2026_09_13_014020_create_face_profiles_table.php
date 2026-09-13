<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'pending',        // menunggu review admin
                'approved',       // disetujui, siap untuk recognition
                'rejected',       // ditolak admin
                're_enroll',      // diminta enrollment ulang
                'disabled'        // dinonaktifkan
            ])->default('pending');

            $table->decimal('overall_quality_score', 5, 4)->nullable();
            $table->integer('sample_count')->default(0);

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();

            $table->timestamps();

            // Satu siswa hanya boleh punya 1 profile aktif pada satu waktu
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_profiles');
    }
};