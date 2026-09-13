<?php

namespace App\Interfaces;

/**
 * Interface untuk service validasi kualitas gambar wajah.
 */
interface FaceQualityValidatorInterface
{
    /**
     * Validasi kualitas gambar wajah.
     *
     * @param string $imagePath Path absolut ke file gambar
     * @return array{
     *     valid: bool,
     *     score: float,
     *     face_detected: bool,
     *     brightness: float,
     *     blur_score: float,
     *     face_box: array|null,
     *     errors: array
     * }
     */
    public function validate(string $imagePath): array;

    /**
     * Cek cepat apakah gambar memenuhi minimum requirement.
     */
    public function isAcceptable(string $imagePath): bool;
}