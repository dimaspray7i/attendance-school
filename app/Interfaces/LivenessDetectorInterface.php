<?php

namespace App\Interfaces;

/**
 * Interface untuk service deteksi liveness / anti-spoofing.
 * 
 * Implementasi bisa menggunakan:
 * - Challenge-response (blink, head movement)
 * - Texture analysis
 * - Depth estimation
 * - ML-based anti-spoofing model
 */
interface LivenessDetectorInterface
{
    /**
     * Deteksi liveness dari satu gambar atau sequence.
     *
     * @param string|array $imageInput Path gambar atau array path untuk sequence
     * @return array{
     *     is_live: bool,
     *     score: float,
     *     spoof_type: string|null,
     *     metadata: array
     * }
     */
    public function detect(string|array $imageInput): array;

    /**
     * Generate challenge untuk user (jika diperlukan).
     * Contoh: "Please blink twice", "Turn your head left"
     */
    public function generateChallenge(): array;
}