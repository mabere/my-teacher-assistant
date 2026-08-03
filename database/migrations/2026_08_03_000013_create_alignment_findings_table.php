<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alignment_findings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teaching_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_content_version_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('severity')->default('info');
            $table->string('category');
            $table->string('title');
            $table->text('detail');
            $table->text('recommendation')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->unique(['generated_content_version_id', 'code']);
            $table->index(['teaching_project_id', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alignment_findings');
    }
};
