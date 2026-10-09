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
        Schema::create('point_opportunities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('analysis_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('analysis_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('opportunity_type')->index();
            $table->unsignedBigInteger('occurred_at_ms');
            $table->decimal('confidence', 5, 4);
            $table->string('trigger');
            $table->text('explanation');
            $table->text('recommended_action')->nullable();
            $table->json('evidence')->nullable();
            $table->string('validation_status')->default('pending')->index();
            $table->timestamps();

            $table->index(['analysis_id', 'confidence']);
            $table->index(['analysis_id', 'occurred_at_ms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_opportunities');
    }
};
