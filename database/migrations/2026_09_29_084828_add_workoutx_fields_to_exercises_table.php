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
        Schema::table('exercises', function (Blueprint $table) {
            $table->string('workoutx_id')->nullable();
            $table->string('target_muscle')->nullable();
            $table->json('instructions')->nullable();
            $table->string('mechanic')->nullalbe();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn([
                'workoutx_id',
                'target_muscle',
                'instructions',
                'mechanic',
            ]);
        });
    }
};
