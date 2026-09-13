<?php

namespace App\Providers;

use App\Interfaces\FaceEmbeddingGeneratorInterface;
use App\Interfaces\FaceQualityValidatorInterface;
use App\Interfaces\LivenessDetectorInterface;
use App\Services\FaceQualityValidator;
use App\Services\StubEmbeddingGenerator;
use App\Services\StubLivenessDetector;
use Illuminate\Support\ServiceProvider;

class FaceRecognitionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind interface ke implementation
        // ⚠️ StubEmbeddingGenerator adalah PLACEHOLDER - akan diganti di Tahap 5
        $this->app->bind(FaceEmbeddingGeneratorInterface::class, StubEmbeddingGenerator::class);
        $this->app->bind(FaceQualityValidatorInterface::class, FaceQualityValidator::class);
        $this->app->bind(LivenessDetectorInterface::class, StubLivenessDetector::class);
    }

    public function boot(): void
    {
        //
    }
}