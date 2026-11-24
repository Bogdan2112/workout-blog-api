<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gym_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('description');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('capacity');
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['gym_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};
