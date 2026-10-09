<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('athlete_match_record', function (Blueprint $table) {
            $table->foreignUlid('match_record_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('athlete_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->primary(['match_record_id', 'athlete_id']);
            $table->unique(['match_record_id', 'position']);
            $table->index(['athlete_id', 'match_record_id']);
        });

        DB::table('match_records')
            ->select(['id', 'athlete_id'])
            ->orderBy('id')
            ->chunkById(500, function ($matches): void {
                $timestamp = now();
                $rows = $matches->map(fn (object $match): array => [
                    'match_record_id' => $match->id,
                    'athlete_id' => $match->athlete_id,
                    'position' => 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])->all();

                DB::table('athlete_match_record')->insertOrIgnore($rows);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_match_record');
    }
};
