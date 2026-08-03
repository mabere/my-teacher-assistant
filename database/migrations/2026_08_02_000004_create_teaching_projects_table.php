<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_profile_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('topic');
            $table->text('learning_outcomes')->nullable();
            $table->text('learning_objectives');
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('teaching_model')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['class_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_projects');
    }
};
