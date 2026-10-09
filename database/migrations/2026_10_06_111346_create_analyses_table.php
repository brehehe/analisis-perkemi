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
        Schema::create('analyses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('match_record_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('video_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('athlete_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('queued')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('current_step')->nullable();
            $table->string('model_version')->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedSmallInteger('retry_count')->default(0);
            $table->timestamps();

            $table->index(['match_record_id', 'created_at']);
            $table->index(['athlete_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
