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
        Schema::create('athletes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('coach_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identifier')->unique();
            $table->string('name');
            $table->string('gender', 16)->index();
            $table->date('date_of_birth')->nullable();
            $table->string('category')->index();
            $table->decimal('weight_class', 5, 2)->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->string('status')->default('active')->index();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['club_id', 'status']);
            $table->index(['coach_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};
