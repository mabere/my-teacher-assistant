<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('education_level');
            $table->string('grade');
            $table->string('subject');
            $table->unsignedSmallInteger('student_count')->nullable();
            $table->string('ability_profile');
            $table->text('facilities')->nullable();
            $table->text('local_context')->nullable();
            $table->text('differentiation_needs')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'education_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_profiles');
    }
};
