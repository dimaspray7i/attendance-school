<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: X RPL 1, XI TKJ 2
            $table->string('code')->unique(); // Contoh: XRPL1
            $table->foreignId('major_id')->constrained()->cascadeOnDelete();
            $table->integer('grade'); // 10, 11, 12
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};