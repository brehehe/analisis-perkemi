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
        Schema::create('analysis_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('analysis_id')->constrained()->cascadeOnDelete();
            $table->string('event_type')->index();
            $table->unsignedBigInteger('occurred_at_ms');
            $table->decimal('confidence', 5, 4);
            $table->string('title');
            $table->text('description');
            $table->json('evidence')->nullable();
            $table->string('validation_status')->default('pending')->index();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('validated_at')->nullable();
            $table->timestamps();

            $table->index(['analysis_id', 'occurred_at_ms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_events');
    }
};
