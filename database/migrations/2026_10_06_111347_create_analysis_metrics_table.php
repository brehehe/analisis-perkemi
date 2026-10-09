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
        Schema::create('analysis_metrics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('analysis_id')->constrained()->cascadeOnDelete();
            $table->string('category')->index();
            $table->string('name');
            $table->decimal('score', 5, 2);
            $table->json('evidence')->nullable();
            $table->timestamps();

            $table->unique(['analysis_id', 'category', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_metrics');
    }
};
