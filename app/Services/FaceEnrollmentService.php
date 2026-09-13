<?php

namespace App\Services;

use App\Interfaces\FaceEmbeddingGeneratorInterface;
use App\Interfaces\FaceQualityValidatorInterface;
use App\Interfaces\LivenessDetectorInterface;
use App\Models\FaceEmbedding;
use App\Models\FaceEnrollmentLog;
use App\Models\FaceProfile;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceEnrollmentService
{
    public function __construct(
        private FaceEmbeddingGeneratorInterface $embeddingGenerator,
        private FaceQualityValidatorInterface $qualityValidator,
        private LivenessDetectorInterface $livenessDetector
    ) {}

    /**
     * Proses submission enrollment dari siswa.
     * 
     * @param Student $student
     * @param array $samples Array of ['type' => string, 'path' => string]
     * @return FaceProfile
     */
    public function submitEnrollment(Student $student, array $samples): FaceProfile
    {
        return DB::transaction(function () use ($student, $samples) {
            // Hapus profile lama jika ada (re-enroll)
            $existingProfile = $student->faceProfile;
            if ($existingProfile && in_array($existingProfile->status, ['rejected', 're_enroll'])) {
                $existingProfile->embeddings()->delete();
                $existingProfile->delete();
            }

            // Buat profile baru
            $profile = FaceProfile::create([
                'student_id' => $student->id,
                'status' => 'pending',
                'sample_count' => 0,
            ]);

            $totalQuality = 0;
            $processedCount = 0;
            $isPrimary = true;

            foreach ($samples as $sample) {
                $tempPath = $sample['path'];
                $sampleType = $sample['type'];

                try {
                    // 1. Validasi kualitas
                    $qualityResult = $this->qualityValidator->validate($tempPath);
                    if (!$qualityResult['valid']) {
                        Log::warning("Sample {$sampleType} gagal validasi kualitas", [
                            'student_id' => $student->id,
                            'errors' => $qualityResult['errors'],
                        ]);
                        // Hapus file temporary
                        @unlink($tempPath);
                        continue;
                    }

                    // 2. Generate embedding
                    $embeddingResult = $this->embeddingGenerator->generate($tempPath);

                    // 3. Simpan gambar ke private storage
                    $storedPath = $this->storeImage($student, $profile, $sampleType, $tempPath);

                    // 4. Simpan embedding
                    FaceEmbedding::create([
                        'face_profile_id' => $profile->id,
                        'sample_type' => $sampleType,
                        'embedding_data' => $embeddingResult['embedding'],
                        'embedding_dimension' => $embeddingResult['dimension'],
                        'quality_score' => $qualityResult['score'],
                        'image_path' => $storedPath,
                        'is_primary' => $isPrimary,
                        'captured_at' => now(),
                        'metadata' => [
                            'brightness' => $qualityResult['brightness'],
                            'blur_score' => $qualityResult['blur_score'],
                            'face_box' => $qualityResult['face_box'],
                            'confidence' => $embeddingResult['confidence'],
                        ],
                    ]);

                    $totalQuality += $qualityResult['score'];
                    $processedCount++;
                    $isPrimary = false;

                    // Hapus file temporary
                    @unlink($tempPath);

                } catch (\Throwable $e) {
                    Log::error("Error processing sample {$sampleType}", [
                        'student_id' => $student->id,
                        'error' => $e->getMessage(),
                    ]);
                    @unlink($tempPath);
                    continue;
                }
            }

            if ($processedCount === 0) {
                $profile->delete();
                throw new \RuntimeException('Tidak ada sampel wajah yang valid. Silakan coba lagi.');
            }

            // Update profile
            $profile->update([
                'sample_count' => $processedCount,
                'overall_quality_score' => $totalQuality / $processedCount,
            ]);

            // Log
            $this->logAction($student, $profile, 'submitted', null, [
                'sample_count' => $processedCount,
                'types' => array_column($samples, 'type'),
            ]);

            return $profile->fresh();
        });
    }

    /**
     * Approve enrollment oleh admin.
     */
    public function approve(FaceProfile $profile, User $admin, ?string $notes = null): void
    {
        DB::transaction(function () use ($profile, $admin, $notes) {
            $profile->update([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'enrolled_at' => now(),
                'rejection_reason' => null,
                'admin_notes' => $notes,
            ]);

            $this->logAction($profile->student, $profile, 'approved', $admin->id, [
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Reject enrollment oleh admin.
     */
    public function reject(FaceProfile $profile, User $admin, string $reason): void
    {
        DB::transaction(function () use ($profile, $admin, $reason) {
            $profile->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->logAction($profile->student, $profile, 'rejected', $admin->id, [
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Minta siswa re-enroll.
     */
    public function requestReEnroll(FaceProfile $profile, User $admin, string $reason): void
    {
        DB::transaction(function () use ($profile, $admin, $reason) {
            $profile->update([
                'status' => 're_enroll',
                'rejection_reason' => $reason,
            ]);

            $this->logAction($profile->student, $profile, 're_enroll_requested', $admin->id, [
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Disable face profile.
     */
    public function disable(FaceProfile $profile, User $admin, string $reason): void
    {
        DB::transaction(function () use ($profile, $admin, $reason) {
            $profile->update([
                'status' => 'disabled',
                'disabled_at' => now(),
                'admin_notes' => $reason,
            ]);

            $this->logAction($profile->student, $profile, 'disabled', $admin->id, [
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Simpan gambar ke private storage.
     */
    private function storeImage(Student $student, FaceProfile $profile, string $type, string $tempPath): string
    {
        $filename = sprintf(
            'student_%d/profile_%d/%s_%s.jpg',
            $student->id,
            $profile->id,
            $type,
            now()->format('Ymd_His')
        );

        Storage::disk('local')->put($filename, file_get_contents($tempPath));

        return $filename;
    }

    /**
     * Log aksi enrollment.
     */
    private function logAction(
        Student $student,
        ?FaceProfile $profile,
        string $action,
        ?int $performedBy,
        ?array $metadata
    ): void {
        FaceEnrollmentLog::create([
            'student_id' => $student->id,
            'face_profile_id' => $profile?->id,
            'action' => $action,
            'performed_by' => $performedBy,
            'metadata' => $metadata,
        ]);
    }
}