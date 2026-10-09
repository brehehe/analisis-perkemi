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
        Schema::create('training_recommendations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('analysis_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('athlete_id')->constrained()->cascadeOnDelete();
            $table->string('priority');
            $table->string('drill');
            $table->string('frequency')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('target_metric')->nullable();
            $table->decimal('target_score', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['athlete_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_recommendations');
    }
};
