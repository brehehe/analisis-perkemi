<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('match_record_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->string('original_name')->nullable();
            $table->string('storage_path')->nullable();
            $table->text('external_url')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('resolution')->nullable();
            $table->decimal('fps', 6, 2)->nullable();
            $table->string('codec')->nullable();
            $table->string('processing_status')->default('uploaded')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['match_record_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
