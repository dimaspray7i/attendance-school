<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('face_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('sample_type', ['front', 'left', 'right', 'challenge']);
            $table->longText('embedding_data'); // JSON vector embedding
            $table->integer('embedding_dimension')->default(128);
            $table->decimal('quality_score', 5, 4)->nullable();
            $table->string('image_path'); // path ke foto sampel (private storage)
            $table->boolean('is_primary')->default(false);
            $table->timestamp('captured_at');
            $table->json('metadata')->nullable(); // brightness, blur, face_box, dll
            $table->timestamps();

            $table->index(['face_profile_id', 'sample_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_embeddings');
    }
};