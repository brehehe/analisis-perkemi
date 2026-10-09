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
        Schema::table('match_records', function (Blueprint $table) {
            $table->string('division', 16)->nullable()->after('match_type');
            $table->string('opponent_name')->nullable()->change();
        });

        DB::table('match_records')
            ->select(['id', 'athlete_id'])
            ->whereNull('division')
            ->orderBy('id')
            ->chunkById(500, function ($matches): void {
                $genders = DB::table('athletes')
                    ->whereIn('id', $matches->pluck('athlete_id'))
                    ->pluck('gender', 'id');

                foreach ($matches as $match) {
                    DB::table('match_records')
                        ->where('id', $match->id)
                        ->update(['division' => $genders[$match->athlete_id] ?? 'male']);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_records', function (Blueprint $table) {
            $table->dropColumn('division');
        });

        DB::table('match_records')
            ->whereNull('opponent_name')
            ->update(['opponent_name' => '—']);

        Schema::table('match_records', function (Blueprint $table) {
            $table->string('opponent_name')->nullable(false)->change();
        });
    }
};
