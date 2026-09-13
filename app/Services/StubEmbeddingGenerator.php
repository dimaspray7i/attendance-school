<?php

namespace App\Services;

use App\Interfaces\FaceEmbeddingGeneratorInterface;
use Illuminate\Support\Facades\Log;

/**
 * ⚠️ PLACEHOLDER IMPLEMENTATION ⚠️
 * 
 * Class ini adalah stub yang akan DIGANTI di Tahap 5 dengan implementasi nyata.
 * Tujuan stub ini hanya agar flow enrollment dapat diuji end-to-end.
 * 
 * JANGAN gunakan stub ini untuk production.
 * Vektor yang dihasilkan adalah RANDOM dan tidak bermakna biometrik.
 */
class StubEmbeddingGenerator implements FaceEmbeddingGeneratorInterface
{
    private int $dimension = 128;

    public function __construct()
    {
        Log::warning('[StubEmbeddingGenerator] Menggunakan PLACEHOLDER implementation. '
            . 'Ini akan diganti di Tahap 5 dengan AI service nyata.');
    }

    public function generate(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new \RuntimeException("Image file not found: {$imagePath}");
        }

        // Validasi sederhana: cek apakah file adalah gambar
        $imageInfo = @getimagesize($imagePath);
        if ($imageInfo === false) {
            throw new \RuntimeException("Invalid image file: {$imagePath}");
        }

        // PLACEHOLDER: Generate random vector
        // Di implementasi nyata, ini akan memanggil AI model
        $embedding = [];
        for ($i = 0; $i < $this->dimension; $i++) {
            $embedding[] = mt_rand(-10000, 10000) / 10000.0;
        }

        // Normalize vector
        $magnitude = sqrt(array_sum(array_map(fn($x) => $x * $x, $embedding)));
        if ($magnitude > 0) {
            $embedding = array_map(fn($x) => $x / $magnitude, $embedding);
        }

        return [
            'embedding' => $embedding,
            'dimension' => $this->dimension,
            'confidence' => 0.85, // Placeholder confidence
        ];
    }

    public function compare(array $embeddingA, array $embeddingB): float
    {
        if (count($embeddingA) !== count($embeddingB)) {
            return 0.0;
        }

        // Cosine similarity
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($embeddingA); $i++) {
            $dotProduct += $embeddingA[$i] * $embeddingB[$i];
            $magnitudeA += $embeddingA[$i] * $embeddingA[$i];
            $magnitudeB += $embeddingB[$i] * $embeddingB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    public function dimension(): int
    {
        return $this->dimension;
    }
}