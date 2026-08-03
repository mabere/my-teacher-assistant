<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_contents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teaching_project_id')->constrained()->cascadeOnDelete();
            $table->string('content_type')->default('rpp');
            $table->string('status')->default('queued');
            $table->json('input_snapshot')->nullable();
            $table->json('output_payload')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['teaching_project_id', 'content_type', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_contents');
    }
};
