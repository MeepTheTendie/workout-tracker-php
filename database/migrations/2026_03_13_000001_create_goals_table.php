<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained();
            $table->decimal('target_weight', 8, 2);
            $table->integer('target_reps')->default(1);
            $table->unsignedBigInteger('deadline')->nullable();
            $table->boolean('completed')->default(false);
            $table->unsignedBigInteger('created_at');
            $table->unsignedBigInteger('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
