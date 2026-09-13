<?php

namespace App\Services;

use App\Interfaces\LivenessDetectorInterface;

/**
 * ⚠️ PLACEHOLDER IMPLEMENTATION ⚠️
 * 
 * Stub liveness detector. Akan diganti di Tahap 5.
 */
class StubLivenessDetector implements LivenessDetectorInterface
{
    public function detect(string|array $imageInput): array
    {
        // PLACEHOLDER: Selalu return "live" dengan score tinggi
        return [
            'is_live' => true,
            'score' => 0.95,
            'spoof_type' => null,
            'metadata' => [
                'method' => 'stub',
                'warning' => 'Placeholder - akan diganti implementasi nyata',
            ],
        ];
    }

    public function generateChallenge(): array
    {
        $challenges = [
            ['type' => 'blink', 'instruction' => 'Silakan berkedip dua kali'],
            ['type' => 'head_turn', 'instruction' => 'Silakan menoleh ke kiri'],
            ['type' => 'head_turn', 'instruction' => 'Silakan menoleh ke kanan'],
        ];

        return $challenges[array_rand($challenges)];
    }
}