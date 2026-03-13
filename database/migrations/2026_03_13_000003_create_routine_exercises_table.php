<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained();
            $table->integer('order_index')->default(0);
            $table->integer('target_sets')->nullable();
            $table->integer('target_reps')->nullable();
            $table->decimal('target_weight', 8, 2)->nullable();
            $table->unsignedBigInteger('created_at');
            $table->unsignedBigInteger('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_exercises');
    }
};
