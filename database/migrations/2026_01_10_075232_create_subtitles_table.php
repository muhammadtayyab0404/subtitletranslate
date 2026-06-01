<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subtitles', function (Blueprint $table) {
            $table->id();

            // Each subtitle file belongs to a user
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('original_name');              // e.g. movie.srt
            $table->string('stored_name');                // e.g. movie_1700000000.srt
            $table->string('path');                       // e.g. originalsrt/movie_...

            $table->string('status')->default('uploaded'); // uploaded|parsed|processing|done|failed
            $table->unsignedInteger('total_lines')->default(0);

            $table->text('error')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtitles');
    }
};
