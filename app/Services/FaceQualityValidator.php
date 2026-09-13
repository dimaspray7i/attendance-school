<?php

namespace App\Services;

use App\Interfaces\FaceQualityValidatorInterface;

/**
 * Basic face quality validator.
 * 
 * Implementasi saat ini melakukan validasi dasar (ukuran file, dimensi, format).
 * Di Tahap 5, ini akan ditingkatkan dengan face detection & quality assessment
 * menggunakan AI model (blur detection, brightness, face landmark, dll).
 */
class FaceQualityValidator implements FaceQualityValidatorInterface
{
    private const MIN_WIDTH = 200;
    private const MIN_HEIGHT = 200;
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    public function validate(string $imagePath): array
    {
        $errors = [];
        $score = 1.0;

        // Cek file exists
        if (!file_exists($imagePath)) {
            return [
                'valid' => false,
                'score' => 0.0,
                'face_detected' => false,
                'brightness' => 0.0,
                'blur_score' => 0.0,
                'face_box' => null,
                'errors' => ['File tidak ditemukan'],
            ];
        }

        // Cek ukuran file
        $fileSize = filesize($imagePath);
        if ($fileSize > self::MAX_FILE_SIZE) {
            $errors[] = 'Ukuran file terlalu besar (maks 5MB)';
            $score -= 0.3;
        }

        // Cek format & dimensi
        $imageInfo = @getimagesize($imagePath);
        if ($imageInfo === false) {
            return [
                'valid' => false,
                'score' => 0.0,
                'face_detected' => false,
                'brightness' => 0.0,
                'blur_score' => 0.0,
                'face_box' => null,
                'errors' => ['File bukan gambar valid'],
            ];
        }

        [$width, $height, , $mime] = $imageInfo;
        $mimeString = image_type_to_mime_type($mime);

        if (!in_array($mimeString, self::ALLOWED_MIME)) {
            $errors[] = 'Format gambar tidak didukung';
            $score -= 0.3;
        }

        if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
            $errors[] = 'Resolusi gambar terlalu kecil (min 200x200)';
            $score -= 0.4;
        }

        // PLACEHOLDER: Face detection, brightness, blur
        // Di implementasi nyata, ini akan pakai AI model
        $faceDetected = true; // Placeholder
        $brightness = 0.65;   // Placeholder
        $blurScore = 0.80;    // Placeholder
        $faceBox = [           // Placeholder
            'x' => (int)($width * 0.25),
            'y' => (int)($height * 0.15),
            'width' => (int)($width * 0.5),
            'height' => (int)($height * 0.7),
        ];

        $score = max(0.0, min(1.0, $score));

        return [
            'valid' => empty($errors) && $score >= 0.5,
            'score' => round($score, 4),
            'face_detected' => $faceDetected,
            'brightness' => $brightness,
            'blur_score' => $blurScore,
            'face_box' => $faceBox,
            'errors' => $errors,
        ];
    }

    public function isAcceptable(string $imagePath): bool
    {
        $result = $this->validate($imagePath);
        return $result['valid'];
    }
}