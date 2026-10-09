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
        Schema::create('competition_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->date('starts_at');
            $table->date('ends_at')->nullable();
            $table->string('venue')->nullable();
            $table->string('city')->nullable();
            $table->string('level')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['starts_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_events');
    }
};
