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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('sport_frequency', ['never', 'rarely', 'sometimes', 'often', 'daily']);
            $table->enum('diet_type', ['omnivore', 'vegetarian', 'vegan', 'other']);
            $table->json('food_habits')->nullable();
            $table->unsignedTinyInteger('mental_health_score');
            $table->float('sleep_hours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
