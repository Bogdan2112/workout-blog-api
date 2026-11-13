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
        Schema::create('gym_opening_hours', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('gym_id')
                ->constrained('gyms')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week');

            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();

            $table->timestamps();

            $table->unique(['gym_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_opening_hours');
    }
};
