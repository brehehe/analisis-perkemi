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
        Schema::create('match_records', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('competition_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('athlete_id')->constrained()->restrictOnDelete();
            $table->string('opponent_name');
            $table->string('opponent_club')->nullable();
            $table->dateTimeTz('match_date');
            $table->string('category');
            $table->string('match_type')->default('randori');
            $table->string('result')->default('pending')->index();
            $table->unsignedSmallInteger('athlete_score')->nullable();
            $table->unsignedSmallInteger('opponent_score')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['athlete_id', 'match_date']);
            $table->index(['status', 'match_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_records');
    }
};
