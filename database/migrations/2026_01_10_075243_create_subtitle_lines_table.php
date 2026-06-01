<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subtitle_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subtitle_id')->constrained('subtitles')->cascadeOnDelete();

            $table->unsignedInteger('seq');   // subtitle number
            $table->string('start_time');     // "00:00:01,000"
            $table->string('end_time');       // "00:00:02,000"

            $table->longText('text_original');
            $table->longText('ai_description')->nullable(); // meaning/description from API

            $table->timestamps();

            $table->unique(['subtitle_id', 'seq']);
            $table->index(['subtitle_id', 'seq']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtitle_lines');
    }
};
