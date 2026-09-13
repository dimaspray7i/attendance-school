<?php

namespace App\Interfaces;

/**
 * Interface untuk service yang men-generate embedding vektor dari gambar wajah.
 * 
 * Implementasi nyata bisa menggunakan:
 * - Python service (Flask/FastAPI) dengan face_recognition/InsightFace
 * - PHP library seperti PHP-ML
 * - External API service
 * 
 * Implementasi saat ini (StubEmbeddingGenerator) adalah PLACEHOLDER
 * yang akan diganti di Tahap 5 dengan implementation nyata.
 */
interface FaceEmbeddingGeneratorInterface
{
    /**
     * Generate embedding vector dari gambar wajah.
     *
     * @param string $imagePath Path absolut ke file gambar
     * @return array{embedding: array, dimension: int, confidence: float}
     * @throws \RuntimeException jika gambar tidak valid atau wajah tidak terdeteksi
     */
    public function generate(string $imagePath): array;

    /**
     * Bandingkan dua embedding untuk menghitung similarity.
     *
     * @param array $embeddingA
     * @param array $embeddingB
     * @return float Similarity score 0.0 - 1.0
     */
    public function compare(array $embeddingA, array $embeddingB): float;

    /**
     * Dimension embedding yang dihasilkan service ini.
     */
    public function dimension(): int;
}